<?php

use App\Models\Tenant;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\Queue;
use Laravel\Passport\Passport;
use Livewire\Livewire;
use Modules\Accounting\Jobs\GenerateAndSendReportJob;
use Modules\Accounting\Models\Account;
use Modules\Accounting\Models\AccountType;
use Modules\Accounting\Models\JournalEntry;
use Modules\Accounting\Models\JournalEntryLine;
use Modules\Admin\Filament\Pages\Reports\CashFlowStatementReport;
use Modules\Admin\Models\Admin;
use Modules\Authorization\Models\Role;
use Modules\User\Models\User;
use Tests\TestCase;

uses(TestCase::class, DatabaseMigrations::class);

beforeEach(function () {
    $this->tenant = Tenant::create();
    $this->tenant->domains()->create(['domain' => 'tenant1.localhost']);
    tenancy()->initialize($this->tenant);

    $this->user = User::factory()->create();
    $role = Role::create(['name' => 'accountant', 'guard_name' => 'web']);
    $this->user->assignRole($role);
    Passport::actingAs($this->user);

    $this->admin = Admin::factory()->create();
    $this->admin->assignRole('super_admin');
    $this->actingAs($this->admin, 'admin');

    $this->currAssetType = AccountType::firstOrCreate(['type' => 'current_assets', 'account_group' => 'assets']);
    $this->nonCurrAssetType = AccountType::firstOrCreate(['type' => 'non_current_assets', 'account_group' => 'assets']);
    $this->currLiabType = AccountType::firstOrCreate(['type' => 'current_liabilities', 'account_group' => 'liabilities']);
    $this->nonCurrLiabType = AccountType::firstOrCreate(['type' => 'non_current_liabilities', 'account_group' => 'liabilities']);
    $this->revType = AccountType::firstOrCreate(['type' => 'operating_revenue', 'account_group' => 'revenues']);
    $this->expType = AccountType::firstOrCreate(['type' => 'operating_expenses', 'account_group' => 'expenses']);
    $this->equityType = AccountType::firstOrCreate(['type' => 'equity_capital', 'account_group' => 'equity']);

    $this->cashAcc = Account::factory()->create(['name' => 'Main Cash', 'number' => 1010, 'account_type_id' => $this->currAssetType->id]);
    $this->salesAcc = Account::factory()->create(['name' => 'Service Revenue', 'number' => 4010, 'account_type_id' => $this->revType->id]);
    $this->rentAcc = Account::factory()->create(['name' => 'Office Rent', 'number' => 5010, 'account_type_id' => $this->expType->id]);
    $this->equipAcc = Account::factory()->create(['name' => 'Office Equipment', 'number' => 1510, 'account_type_id' => $this->nonCurrAssetType->id]);
    $this->loanAcc = Account::factory()->create(['name' => 'Bank Loan', 'number' => 2510, 'account_type_id' => $this->nonCurrLiabType->id]);
});

afterEach(function () {
    if (tenancy()->initialized) {
        $tenant = tenancy()->tenant;
        tenancy()->end();
        $tenant->delete();
    }
});

test('cash flow statement computes operating, investing and financing flows and reconciles cash', function () {
    // 1. Operating: Sales Revenue $10,000
    $entry1 = JournalEntry::create([
        'reference' => 'JE-REV', 'date' => '2026-03-01', 'description' => 'Sales Revenue', 'total_debit' => 10000, 'total_credit' => 10000, 'type' => 'journal', 'status' => 'approved',
    ]);
    JournalEntryLine::create(['journal_entry_id' => $entry1->id, 'account_id' => $this->cashAcc->id, 'debit' => 10000, 'credit' => 0, 'date' => '2026-03-01', 'source_type' => 'user', 'source_reference' => (string)$this->user->id]);
    JournalEntryLine::create(['journal_entry_id' => $entry1->id, 'account_id' => $this->salesAcc->id, 'debit' => 0, 'credit' => 10000, 'date' => '2026-03-01', 'source_type' => 'user', 'source_reference' => (string)$this->user->id]);

    // 2. Operating: Rent Expense $3,000
    $entry2 = JournalEntry::create([
        'reference' => 'JE-EXP', 'date' => '2026-03-05', 'description' => 'Office Rent', 'total_debit' => 3000, 'total_credit' => 3000, 'type' => 'journal', 'status' => 'approved',
    ]);
    JournalEntryLine::create(['journal_entry_id' => $entry2->id, 'account_id' => $this->rentAcc->id, 'debit' => 3000, 'credit' => 0, 'date' => '2026-03-05', 'source_type' => 'user', 'source_reference' => (string)$this->user->id]);
    JournalEntryLine::create(['journal_entry_id' => $entry2->id, 'account_id' => $this->cashAcc->id, 'debit' => 0, 'credit' => 3000, 'date' => '2026-03-05', 'source_type' => 'user', 'source_reference' => (string)$this->user->id]);

    // 3. Investing: Equipment purchase $2,000
    $entry3 = JournalEntry::create([
        'reference' => 'JE-INV', 'date' => '2026-03-10', 'description' => 'Equipment Purchase', 'total_debit' => 2000, 'total_credit' => 2000, 'type' => 'journal', 'status' => 'approved',
    ]);
    JournalEntryLine::create(['journal_entry_id' => $entry3->id, 'account_id' => $this->equipAcc->id, 'debit' => 2000, 'credit' => 0, 'date' => '2026-03-10', 'source_type' => 'user', 'source_reference' => (string)$this->user->id]);
    JournalEntryLine::create(['journal_entry_id' => $entry3->id, 'account_id' => $this->cashAcc->id, 'debit' => 0, 'credit' => 2000, 'date' => '2026-03-10', 'source_type' => 'user', 'source_reference' => (string)$this->user->id]);

    // 4. Financing: Bank loan borrowing $5,000
    $entry4 = JournalEntry::create([
        'reference' => 'JE-FIN', 'date' => '2026-03-15', 'description' => 'Bank Loan Borrowing', 'total_debit' => 5000, 'total_credit' => 5000, 'type' => 'journal', 'status' => 'approved',
    ]);
    JournalEntryLine::create(['journal_entry_id' => $entry4->id, 'account_id' => $this->cashAcc->id, 'debit' => 5000, 'credit' => 0, 'date' => '2026-03-15', 'source_type' => 'user', 'source_reference' => (string)$this->user->id]);
    JournalEntryLine::create(['journal_entry_id' => $entry4->id, 'account_id' => $this->loanAcc->id, 'debit' => 0, 'credit' => 5000, 'date' => '2026-03-15', 'source_type' => 'user', 'source_reference' => (string)$this->user->id]);

    $response = $this->getJson('/api/v1/accounting/reports/cash-flow?startDate=2026-01-01&endDate=2026-12-31');
    $response->assertStatus(200)
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.net_income', 7000)
        ->assertJsonPath('data.net_operating_cash_flow', 7000)
        ->assertJsonPath('data.net_investing_cash_flow', -2000)
        ->assertJsonPath('data.net_financing_cash_flow', 5000)
        ->assertJsonPath('data.ending_cash', 10000)
        ->assertJsonPath('data.is_balanced', true);
});

test('cash flow report export queues background job', function () {
    Queue::fake();

    $this->getJson('/api/v1/accounting/reports/cash-flow?export=pdf&email=auditor@test.com')
        ->assertStatus(200)
        ->assertJsonPath('success', true);

    Queue::assertPushed(GenerateAndSendReportJob::class, function ($job) {
        return $job->reportType === 'cash-flow' && $job->recipientEmail === 'auditor@test.com';
    });
});

test('admin can access and render cash flow report page in Filament', function () {
    Livewire::test(CashFlowStatementReport::class)
        ->assertSuccessful()
        ->assertSee('STATEMENT OF CASH FLOWS')
        ->assertSee('Operating Activities')
        ->assertSee('Cash Reconciliation');
});
