<?php

use App\Models\Tenant;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\Queue;
use Livewire\Livewire;
use Modules\Accounting\Jobs\GenerateAndSendReportJob;
use Modules\Accounting\Models\Account;
use Modules\Accounting\Models\AccountType;
use Modules\Accounting\Models\JournalEntry;
use Modules\Accounting\Models\JournalEntryLine;
use Modules\Admin\Filament\Pages\Reports\BalanceSheetReport;
use Modules\Admin\Filament\Pages\Reports\GeneralLedgerReport;
use Modules\Admin\Filament\Pages\Reports\IncomeStatementReport;
use Modules\Admin\Filament\Pages\Reports\TrialBalanceReport;
use Modules\Admin\Models\Admin;
use Modules\Branch\Models\Branch;
use Tests\TestCase;

uses(TestCase::class, DatabaseMigrations::class);

beforeEach(function () {
    $this->tenant = Tenant::create();
    $this->tenant->domains()->create(['domain' => 'tenant1.localhost']);
    tenancy()->initialize($this->tenant);

    $this->admin = Admin::factory()->create();
    $this->admin->assignRole('super_admin');
    $this->actingAs($this->admin, 'admin');

    $this->branchA = Branch::create([
        'name' => 'Cairo Branch',
        'code' => 'CAI',
        'phone' => '0101111111',
        'address' => 'Cairo',
        'active' => 1,
    ]);

    $this->branchB = Branch::create([
        'name' => 'Alex Branch',
        'code' => 'ALX',
        'phone' => '0102222222',
        'address' => 'Alexandria',
        'active' => 1,
    ]);

    $this->cashType = AccountType::where('type', 'current_assets')->first();
    $this->revenueType = AccountType::where('type', 'operating_revenue')->first();
    $this->expenseType = AccountType::where('type', 'operating_expenses')->first();
    $this->equityType = AccountType::where('type', 'retained_earnings')->first();

    $this->cashAccount = Account::factory()->create([
        'name' => 'Main Cash',
        'account_type_id' => $this->cashType->id,
    ]);

    $this->salesAccount = Account::factory()->create([
        'name' => 'Sales Revenue',
        'account_type_id' => $this->revenueType->id,
    ]);

    $this->rentAccount = Account::factory()->create([
        'name' => 'Office Rent',
        'account_type_id' => $this->expenseType->id,
    ]);

    $this->capitalAccount = Account::factory()->create([
        'name' => 'Owner Capital',
        'account_type_id' => $this->equityType->id,
    ]);
});

afterEach(function () {
    if (tenancy()->initialized) {
        $tenant = tenancy()->tenant;
        tenancy()->end();
        $tenant->delete();
    }
});

test('admin can access and render Trial Balance report page in Filament', function () {
    $entry = JournalEntry::factory()->create([
        'date' => '2026-04-01',
        'total_debit' => 5000,
        'total_credit' => 5000,
        'branch_id' => $this->branchA->id,
    ]);
    JournalEntryLine::factory()->create(['journal_entry_id' => $entry->id, 'account_id' => $this->cashAccount->id, 'branch_id' => $this->branchA->id, 'debit' => 5000, 'credit' => 0]);
    JournalEntryLine::factory()->create(['journal_entry_id' => $entry->id, 'account_id' => $this->capitalAccount->id, 'branch_id' => $this->branchA->id, 'debit' => 0, 'credit' => 5000]);

    Livewire::test(TrialBalanceReport::class)
        ->set('endDate', '2026-04-30')
        ->assertSuccessful()
        ->assertSee('Trial Balance')
        ->assertSee('Total Debits')
        ->assertSee('Total Credits')
        ->assertSee('5,000.00')
        ->assertSee('Balanced');
});

test('admin can trigger email report queue on Trial Balance page', function () {
    Queue::fake();

    Livewire::test(TrialBalanceReport::class)
        ->callAction('email_report', [
            'email'  => 'accountant@test.com',
            'format' => 'both',
        ])
        ->assertHasNoActionErrors();

    Queue::assertPushed(GenerateAndSendReportJob::class, function ($job) {
        return $job->reportType === 'trial-balance'
            && $job->recipientEmail === 'accountant@test.com';
    });
});

test('admin can access and render General Ledger report page in Filament', function () {
    $entry = JournalEntry::factory()->create([
        'date' => '2026-04-10',
        'reference' => 'JE-GL-TEST',
        'description' => 'Cash Deposit',
        'total_debit' => 3000,
        'total_credit' => 3000,
        'branch_id' => $this->branchA->id,
    ]);
    JournalEntryLine::factory()->create(['journal_entry_id' => $entry->id, 'account_id' => $this->cashAccount->id, 'branch_id' => $this->branchA->id, 'debit' => 3000, 'credit' => 0]);
    JournalEntryLine::factory()->create(['journal_entry_id' => $entry->id, 'account_id' => $this->capitalAccount->id, 'branch_id' => $this->branchA->id, 'debit' => 0, 'credit' => 3000]);

    Livewire::test(GeneralLedgerReport::class)
        ->set('startDate', '2026-04-01')
        ->set('endDate', '2026-04-30')
        ->set('accountId', $this->cashAccount->id)
        ->assertSuccessful()
        ->assertSee('General Ledger')
        ->assertSee('Main Cash')
        ->assertSee('JE-GL-TEST')
        ->assertSee('3,000.00');
});

test('admin can access and render Income Statement report page in Filament', function () {
    // Revenue entry
    $rev = JournalEntry::factory()->create(['date' => '2026-04-05', 'total_debit' => 12000, 'total_credit' => 12000]);
    JournalEntryLine::factory()->create(['journal_entry_id' => $rev->id, 'account_id' => $this->cashAccount->id, 'debit' => 12000]);
    JournalEntryLine::factory()->create(['journal_entry_id' => $rev->id, 'account_id' => $this->salesAccount->id, 'credit' => 12000]);

    // Expense entry
    $exp = JournalEntry::factory()->create(['date' => '2026-04-06', 'total_debit' => 4500, 'total_credit' => 4500]);
    JournalEntryLine::factory()->create(['journal_entry_id' => $exp->id, 'account_id' => $this->rentAccount->id, 'debit' => 4500]);
    JournalEntryLine::factory()->create(['journal_entry_id' => $exp->id, 'account_id' => $this->cashAccount->id, 'credit' => 4500]);

    Livewire::test(IncomeStatementReport::class)
        ->set('startDate', '2026-04-01')
        ->set('endDate', '2026-04-30')
        ->assertSuccessful()
        ->assertSee('Income Statement (Profit & Loss)')
        ->assertSee('Total Revenues')
        ->assertSee('12,000.00')
        ->assertSee('Operating Expenses')
        ->assertSee('4,500.00')
        ->assertSee('7,500.00')
        ->assertSee('Net Income (Profit)');
});

test('admin can access and render Balance Sheet report page in Filament', function () {
    $entry = JournalEntry::factory()->create(['date' => '2026-04-01', 'total_debit' => 8000, 'total_credit' => 8000]);
    JournalEntryLine::factory()->create(['journal_entry_id' => $entry->id, 'account_id' => $this->cashAccount->id, 'debit' => 8000]);
    JournalEntryLine::factory()->create(['journal_entry_id' => $entry->id, 'account_id' => $this->capitalAccount->id, 'credit' => 8000]);

    Livewire::test(BalanceSheetReport::class)
        ->set('endDate', '2026-04-30')
        ->assertSuccessful()
        ->assertSee('Balance Sheet')
        ->assertSee('Total Assets')
        ->assertSee('8,000.00')
        ->assertSee('Balanced (A = L + E)');
});

test('all four report pages are accessible via their registered admin routes', function () {
    $this->get('http://tenant1.localhost/admin/reports/trial-balance')->assertStatus(200);
    $this->get('http://tenant1.localhost/admin/reports/general-ledger')->assertStatus(200);
    $this->get('http://tenant1.localhost/admin/reports/income-statement')->assertStatus(200);
    $this->get('http://tenant1.localhost/admin/reports/balance-sheet')->assertStatus(200);
});
