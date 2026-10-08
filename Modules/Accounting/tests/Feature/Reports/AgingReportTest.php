<?php

use App\Models\Tenant;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Passport\Passport;
use Livewire\Livewire;
use Modules\Accounting\Models\Account;
use Modules\Accounting\Models\AccountType;
use Modules\Accounting\Models\JournalEntry;
use Modules\Accounting\Models\JournalEntryLine;
use Modules\Accounting\Services\Reports\AgingReportService;
use Modules\Admin\Filament\Pages\Reports\AgingReportPage;
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
    $this->currLiabType = AccountType::firstOrCreate(['type' => 'current_liabilities', 'account_group' => 'liabilities']);
    $this->revType = AccountType::firstOrCreate(['type' => 'operating_revenue', 'account_group' => 'revenues']);
    $this->expType = AccountType::firstOrCreate(['type' => 'operating_expenses', 'account_group' => 'expenses']);

    $this->arAccount = Account::factory()->create([
        'name'            => 'Accounts Receivable - Trade',
        'number'          => 1200,
        'account_type_id' => $this->currAssetType->id,
    ]);

    $this->apAccount = Account::factory()->create([
        'name'            => 'Accounts Payable - Vendors',
        'number'          => 2010,
        'account_type_id' => $this->currLiabType->id,
    ]);

    $this->salesAccount = Account::factory()->create([
        'name'            => 'Revenue Sales',
        'number'          => 4001,
        'account_type_id' => $this->revType->id,
    ]);

    $this->suppliesExpense = Account::factory()->create([
        'name'            => 'Office Supplies',
        'number'          => 5055,
        'account_type_id' => $this->expType->id,
    ]);
});

afterEach(function () {
    if (tenancy()->initialized) {
        $tenant = tenancy()->tenant;
        tenancy()->end();
        $tenant->delete();
    }
});

test('AR aging partitions unpaid receivables into correct 1-30 and 31-60 day buckets', function () {
    $service = app(AgingReportService::class);
    $asOf = Carbon::parse('2026-06-30');

    // 1. Invoice dated 20 days before cutoff (2026-06-10) => $1,500 (bucket: 1-30)
    $entry1 = JournalEntry::create([
        'reference'    => 'INV-001',
        'date'         => '2026-06-10 10:00:00',
        'description'  => 'Client Project Alpha',
        'type'         => 'journal',
        'status'       => 'approved',
        'total_debit'  => 1500.00,
        'total_credit' => 1500.00,
    ]);
    JournalEntryLine::create([
        'source_type'      => 'user',
        'source_reference' => 1,
        'journal_entry_id' => $entry1->id,
        'account_id'       => $this->arAccount->id,
        'debit'            => 1500.00,
        'credit'           => 0.00,
        'date'             => '2026-06-10',
    ]);
    JournalEntryLine::create([
        'source_type'      => 'user',
        'source_reference' => 1,
        'journal_entry_id' => $entry1->id,
        'account_id'       => $this->salesAccount->id,
        'debit'            => 0.00,
        'credit'           => 1500.00,
        'date'             => '2026-06-10',
    ]);

    // 2. Invoice dated 45 days before cutoff (2026-05-16) => $2,500 (bucket: 31-60)
    $entry2 = JournalEntry::create([
        'reference'    => 'INV-002',
        'date'         => '2026-05-16 10:00:00',
        'description'  => 'Client Project Beta',
        'type'         => 'journal',
        'status'       => 'approved',
        'total_debit'  => 2500.00,
        'total_credit' => 2500.00,
    ]);
    JournalEntryLine::create([
        'source_type'      => 'user',
        'source_reference' => 1,
        'journal_entry_id' => $entry2->id,
        'account_id'       => $this->arAccount->id,
        'debit'            => 2500.00,
        'credit'           => 0.00,
        'date'             => '2026-05-16',
    ]);
    JournalEntryLine::create([
        'source_type'      => 'user',
        'source_reference' => 1,
        'journal_entry_id' => $entry2->id,
        'account_id'       => $this->salesAccount->id,
        'debit'            => 0.00,
        'credit'           => 2500.00,
        'date'             => '2026-05-16',
    ]);

    $report = $service->getAgingReport('receivable', '2026-06-30');

    expect($report['rows'])->not->toBeEmpty();
    expect((float) $report['grand_total']['days_1_30'])->toBe(1500.00);
    expect((float) $report['grand_total']['days_31_60'])->toBe(2500.00);
    expect((float) $report['grand_total']['total'])->toBe(4000.00);
});

test('AP aging partitions unpaid vendor bills into 61-90 day bucket', function () {
    $service = app(AgingReportService::class);

    // Bill dated 75 days before cutoff (2026-04-16 vs cutoff 2026-06-30) => $3,200
    $bill = JournalEntry::create([
        'reference'    => 'BILL-099',
        'date'         => '2026-04-16 10:00:00',
        'description'  => 'Office furniture supplier bill',
        'type'         => 'journal',
        'status'       => 'approved',
        'total_debit'  => 3200.00,
        'total_credit' => 3200.00,
    ]);
    JournalEntryLine::create([
        'source_type'      => 'user',
        'source_reference' => 1,
        'journal_entry_id' => $bill->id,
        'account_id'       => $this->suppliesExpense->id,
        'debit'            => 3200.00,
        'credit'           => 0.00,
        'date'             => '2026-04-16',
    ]);
    JournalEntryLine::create([
        'source_type'      => 'user',
        'source_reference' => 1,
        'journal_entry_id' => $bill->id,
        'account_id'       => $this->apAccount->id,
        'debit'            => 0.00,
        'credit'           => 3200.00,
        'date'             => '2026-04-16',
    ]);

    $report = $service->getAgingReport('payable', '2026-06-30');

    expect((float) $report['grand_total']['days_61_90'])->toBe(3200.00);
    expect((float) $report['grand_total']['total'])->toBe(3200.00);
});

test('REST API endpoints return ar-aging and ap-aging successfully', function () {
    $responseAR = $this->getJson('/api/v1/accounting/reports/ar-aging?as_of_date=2026-06-30');
    $responseAR->assertOk()
        ->assertJsonStructure([
            'status',
            'message',
            'data' => [
                'type',
                'type_label',
                'as_of_date',
                'rows',
                'grand_total',
            ],
        ]);

    $responseAP = $this->getJson('/api/v1/accounting/reports/ap-aging?as_of_date=2026-06-30');
    $responseAP->assertOk();
});

test('admin can access and render aging report page in Filament', function () {
    Livewire::test(AgingReportPage::class)
        ->assertSuccessful()
        ->set('type', 'receivable')
        ->assertSee('Accounts Receivable')
        ->set('type', 'payable')
        ->assertSee('Accounts Payable');
});
