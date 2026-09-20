<?php

use App\Models\Tenant;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Livewire\Livewire;
use Modules\Accounting\Models\Account;
use Modules\Accounting\Models\AccountType;
use Modules\Accounting\Models\Budget;
use Modules\Accounting\Models\JournalEntry;
use Modules\Accounting\Models\JournalEntryLine;
use Modules\Admin\Filament\Pages\Dashboard;
use Modules\Admin\Filament\Widgets\AccountingOverviewHeaderWidget;
use Modules\Admin\Filament\Widgets\AccountingStatsOverviewWidget;
use Modules\Admin\Filament\Widgets\BudgetUtilizationChartWidget;
use Modules\Admin\Filament\Widgets\CashflowTrendChartWidget;
use Modules\Admin\Filament\Widgets\LatestJournalEntriesWidget;
use Modules\Admin\Models\Admin;
use Tests\TestCase;

uses(TestCase::class, DatabaseMigrations::class);

beforeEach(function () {
    $this->tenant = Tenant::create();
    $this->tenant->domains()->create(['domain' => 'tenant1.localhost']);
    tenancy()->initialize($this->tenant);

    $this->admin = Admin::factory()->create([
        'name' => 'Fares Admin',
    ]);
    $this->admin->assignRole('super_admin');
    $this->actingAs($this->admin, 'admin');

    $this->revType = AccountType::firstOrCreate(['type' => 'operating_revenue', 'account_group' => 'revenues']);
    $this->expType = AccountType::firstOrCreate(['type' => 'operating_expenses', 'account_group' => 'expenses']);
    $this->cashType = AccountType::firstOrCreate(['type' => 'current_assets', 'account_group' => 'assets']);

    $this->salesAcc = Account::factory()->create(['name' => 'Consulting Revenue', 'account_type_id' => $this->revType->id]);
    $this->rentAcc = Account::factory()->create(['name' => 'Office Space', 'account_type_id' => $this->expType->id]);
    $this->cashAcc = Account::factory()->create(['name' => 'Main Bank', 'account_type_id' => $this->cashType->id]);
});

afterEach(function () {
    if (tenancy()->initialized) {
        $tenant = tenancy()->tenant;
        tenancy()->end();
        $tenant->delete();
    }
});

test('admin can access and render executive accounting dashboard in Filament', function () {
    $response = $this->get('/admin');
    $response->assertSuccessful();
});

test('accounting overview header widget renders correctly with tenant and period info', function () {
    Livewire::test(AccountingOverviewHeaderWidget::class)
        ->assertSuccessful()
        ->assertSee('Welcome back')
        ->assertSee('Fares Admin')
        ->assertSee('Open for Posting')
        ->assertSee('New Journal Entry')
        ->assertSee('Trial Balance')
        ->assertSee('Budgets vs. Actuals');
});

test('accounting stats overview widget calculates live financial metrics and sparklines', function () {
    $currentYear = (int) now()->format('Y');

    // Post $25,000 Revenue and $10,000 Expense
    $entry = JournalEntry::create([
        'reference'   => 'DASH-REV-01',
        'date'        => "{$currentYear}-04-15",
        'description' => 'April Sales Revenue',
        'total_debit' => 25000.00,
        'total_credit'=> 25000.00,
        'type'        => 'journal',
        'status'      => 'approved',
    ]);
    JournalEntryLine::create([
        'journal_entry_id' => $entry->id,
        'account_id'       => $this->cashAcc->id,
        'debit'            => 25000.00,
        'credit'           => 0,
        'date'             => "{$currentYear}-04-15",
        'source_type'      => 'admin',
        'source_reference' => (string) $this->admin->id,
    ]);
    JournalEntryLine::create([
        'journal_entry_id' => $entry->id,
        'account_id'       => $this->salesAcc->id,
        'debit'            => 0,
        'credit'           => 25000.00,
        'date'             => "{$currentYear}-04-15",
        'source_type'      => 'admin',
        'source_reference' => (string) $this->admin->id,
    ]);

    $expEntry = JournalEntry::create([
        'reference'   => 'DASH-EXP-01',
        'date'        => "{$currentYear}-04-20",
        'description' => 'April Office Rent',
        'total_debit' => 10000.00,
        'total_credit'=> 10000.00,
        'type'        => 'journal',
        'status'      => 'approved',
    ]);
    JournalEntryLine::create([
        'journal_entry_id' => $expEntry->id,
        'account_id'       => $this->rentAcc->id,
        'debit'            => 10000.00,
        'credit'           => 0,
        'date'             => "{$currentYear}-04-20",
        'source_type'      => 'admin',
        'source_reference' => (string) $this->admin->id,
    ]);
    JournalEntryLine::create([
        'journal_entry_id' => $expEntry->id,
        'account_id'       => $this->cashAcc->id,
        'debit'            => 0,
        'credit'           => 10000.00,
        'date'             => "{$currentYear}-04-20",
        'source_type'      => 'admin',
        'source_reference' => (string) $this->admin->id,
    ]);

    // Net Profit: $15,000, Revenues: $25,000, Expenses: $10,000, Liquid: $15,000
    Livewire::test(AccountingStatsOverviewWidget::class)
        ->assertSuccessful()
        ->assertSee('Net Profit (YTD)')
        ->assertSee('15,000.00')
        ->assertSee('Revenues (YTD)')
        ->assertSee('25,000.00')
        ->assertSee('Expenses (YTD)')
        ->assertSee('10,000.00')
        ->assertSee('Liquid Cash & Reserves');
});

test('cashflow trend chart and budget utilization widgets render without errors', function () {
    Livewire::test(CashflowTrendChartWidget::class)
        ->assertSuccessful();

    $currentYear = (int) now()->format('Y');
    Budget::create([
        'fiscal_year'      => $currentYear,
        'account_id'       => $this->rentAcc->id,
        'branch_id'        => null,
        'allocated_amount' => 50000.00,
    ]);

    Livewire::test(BudgetUtilizationChartWidget::class)
        ->assertSuccessful();
});

test('latest journal entries table widget renders posted transactions', function () {
    $entry = JournalEntry::create([
        'reference'   => 'LATEST-JE-99',
        'date'        => now()->format('Y-m-d H:i:s'),
        'description' => 'Test Recent Entry Description',
        'total_debit' => 4200.00,
        'total_credit'=> 4200.00,
        'type'        => 'journal',
        'status'      => 'approved',
    ]);

    Livewire::test(LatestJournalEntriesWidget::class)
        ->assertSuccessful()
        ->assertSee('LATEST-JE-99')
        ->assertSee('Test Recent Entry Description')
        ->assertSee('4,200.00');
});
