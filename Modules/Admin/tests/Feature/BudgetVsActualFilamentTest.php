<?php

use App\Models\Tenant;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Livewire\Livewire;
use Modules\Accounting\Models\Account;
use Modules\Accounting\Models\AccountType;
use Modules\Accounting\Models\Budget;
use Modules\Accounting\Models\JournalEntry;
use Modules\Accounting\Models\JournalEntryLine;
use Modules\Admin\Filament\Pages\Reports\BudgetVsActualReport;
use Modules\Admin\Filament\Resources\Budgets\Pages\CreateBudget;
use Modules\Admin\Filament\Resources\Budgets\Pages\ListBudgets;
use Modules\Admin\Models\Admin;
use Modules\Branch\Models\Branch;
use Tests\TestCase;

uses(TestCase::class, DatabaseMigrations::class);

beforeEach(function () {
    $this->tenant = Tenant::create();
    $this->tenant->domains()->create(['domain' => 'budget-tenant.localhost']);
    tenancy()->initialize($this->tenant);

    $this->admin = Admin::factory()->create();
    $this->admin->assignRole('super_admin');
    $this->actingAs($this->admin, 'admin');

    $this->branchA = Branch::create([
        'name' => 'Main Office',
        'code' => 'HQ01',
        'phone' => '0101111111',
        'address' => 'HQ Address',
        'active' => 1,
    ]);

    $this->branchB = Branch::create([
        'name' => 'Secondary Office',
        'code' => 'BR02',
        'phone' => '0102222222',
        'address' => 'Branch Address',
        'active' => 1,
    ]);

    $this->expenseType = AccountType::firstOrCreate(['type' => 'operating_expenses', 'account_group' => 'expenses']);
    $this->revenueType = AccountType::firstOrCreate(['type' => 'operating_revenue', 'account_group' => 'revenues']);

    $this->expenseAccount = Account::factory()->create([
        'number' => 5100,
        'name' => 'Marketing & Advertising',
        'account_type_id' => $this->expenseType->id,
    ]);

    $this->cashAccount = Account::factory()->create([
        'number' => 1010,
        'name' => 'Petty Cash',
    ]);
});

afterEach(function () {
    if (tenancy()->initialized) {
        $tenant = tenancy()->tenant;
        tenancy()->end();
        $tenant->delete();
    }
});

test('admin can access and render Budgets list page in Filament', function () {
    $budget = Budget::create([
        'fiscal_year'      => 2026,
        'account_id'       => $this->expenseAccount->id,
        'branch_id'        => $this->branchA->id,
        'allocated_amount' => 50000.00,
        'notes'            => 'Annual Marketing Budget',
    ]);

    Livewire::test(ListBudgets::class)
        ->assertSuccessful()
        ->assertCanSeeTableRecords([$budget])
        ->assertSee('Marketing & Advertising')
        ->assertSee('50,000.00');
});

test('admin can create budget allocation target via Filament form', function () {
    Livewire::test(CreateBudget::class)
        ->fillForm([
            'fiscal_year'      => 2026,
            'account_id'       => $this->expenseAccount->id,
            'branch_id'        => $this->branchA->id,
            'allocated_amount' => 75000.00,
            'notes'            => 'Q1 revised expansion budget',
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas('budgets', [
        'fiscal_year'      => 2026,
        'account_id'       => $this->expenseAccount->id,
        'branch_id'        => $this->branchA->id,
        'allocated_amount' => 75000.00,
        'created_by'       => $this->admin->id,
    ]);
});

test('admin can view and render Budget vs Actuals report page with variance and utilization', function () {
    // 1. Create a budget for 2026
    Budget::create([
        'fiscal_year'      => 2026,
        'account_id'       => $this->expenseAccount->id,
        'branch_id'        => null, // Consolidated
        'allocated_amount' => 20000.00,
    ]);

    // 2. Post actual expenses in 2026 ($12,000)
    $entry = JournalEntry::create([
        'reference'   => 'EXP-2026-001',
        'date'        => '2026-05-10',
        'description' => 'Marketing Campaign Actual Spend',
        'total_debit' => 12000.00,
        'total_credit'=> 12000.00,
        'type'        => 'journal',
        'status'      => 'approved',
    ]);
    JournalEntryLine::create([
        'journal_entry_id' => $entry->id,
        'account_id'       => $this->expenseAccount->id,
        'debit'            => 12000.00,
        'credit'           => 0,
        'date'             => '2026-05-10',
        'source_type'      => 'admin',
        'source_reference' => (string) $this->admin->id,
    ]);
    JournalEntryLine::create([
        'journal_entry_id' => $entry->id,
        'account_id'       => $this->cashAccount->id,
        'debit'            => 0,
        'credit'           => 12000.00,
        'date'             => '2026-05-10',
        'source_type'      => 'admin',
        'source_reference' => (string) $this->admin->id,
    ]);

    // 3. Test report page rendering
    Livewire::test(BudgetVsActualReport::class)
        ->set('fiscalYear', 2026)
        ->set('branchId', null)
        ->assertSuccessful()
        ->assertSee('BUDGET VS. ACTUAL VARIANCE REPORT')
        ->assertSee('20,000.00') // Budgeted
        ->assertSee('12,000.00') // Actual
        ->assertSee('8,000.00')  // Remaining Variance ($20,000 - $12,000)
        ->assertSee('60.0%')     // Utilization ($12,000 / $20,000 * 100)
        ->assertSee('Within Budget');
});

test('budget vs actual report filters calculations by branch dimension', function () {
    // Budget assigned to branch A
    Budget::create([
        'fiscal_year'      => 2026,
        'account_id'       => $this->expenseAccount->id,
        'branch_id'        => $this->branchA->id,
        'allocated_amount' => 10000.00,
    ]);

    // Actual expense on Branch A: $4,000
    $entryA = JournalEntry::create([
        'reference'   => 'EXP-A',
        'date'        => '2026-03-01',
        'description' => 'Branch A Expense',
        'total_debit' => 4000.00,
        'total_credit'=> 4000.00,
        'type'        => 'journal',
        'status'      => 'approved',
    ]);
    JournalEntryLine::create([
        'journal_entry_id' => $entryA->id,
        'account_id'       => $this->expenseAccount->id,
        'branch_id'        => $this->branchA->id,
        'debit'            => 4000.00,
        'credit'           => 0,
        'date'             => '2026-03-01',
        'source_type'      => 'admin',
        'source_reference' => (string) $this->admin->id,
    ]);
    JournalEntryLine::create([
        'journal_entry_id' => $entryA->id,
        'account_id'       => $this->cashAccount->id,
        'branch_id'        => $this->branchA->id,
        'debit'            => 0,
        'credit'           => 4000.00,
        'date'             => '2026-03-01',
        'source_type'      => 'admin',
        'source_reference' => (string) $this->admin->id,
    ]);

    // Actual expense on Branch B: $3,000
    $entryB = JournalEntry::create([
        'reference'   => 'EXP-B',
        'date'        => '2026-03-05',
        'description' => 'Branch B Expense',
        'total_debit' => 3000.00,
        'total_credit'=> 3000.00,
        'type'        => 'journal',
        'status'      => 'approved',
    ]);
    JournalEntryLine::create([
        'journal_entry_id' => $entryB->id,
        'account_id'       => $this->expenseAccount->id,
        'branch_id'        => $this->branchB->id,
        'debit'            => 3000.00,
        'credit'           => 0,
        'date'             => '2026-03-05',
        'source_type'      => 'admin',
        'source_reference' => (string) $this->admin->id,
    ]);
    JournalEntryLine::create([
        'journal_entry_id' => $entryB->id,
        'account_id'       => $this->cashAccount->id,
        'branch_id'        => $this->branchB->id,
        'debit'            => 0,
        'credit'           => 3000.00,
        'date'             => '2026-03-05',
        'source_type'      => 'admin',
        'source_reference' => (string) $this->admin->id,
    ]);

    // Query for Branch A
    Livewire::test(BudgetVsActualReport::class)
        ->set('fiscalYear', 2026)
        ->set('branchId', $this->branchA->id)
        ->assertSuccessful()
        ->assertSee('4,000.00')  // Actual for Branch A
        ->assertSee('6,000.00')  // Variance ($10,000 - $4,000)
        ->assertSee('40.0%');    // Utilization (4,000 / 10,000)
});
