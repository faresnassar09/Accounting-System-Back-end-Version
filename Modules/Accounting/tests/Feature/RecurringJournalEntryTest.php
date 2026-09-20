<?php

use App\Models\Tenant;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Modules\Accounting\Models\Account;
use Modules\Accounting\Models\AccountType;
use Modules\Accounting\Models\JournalEntry;
use Modules\Accounting\Models\RecurringJournalEntry;
use Modules\Accounting\Models\RecurringJournalEntryLine;
use Modules\Accounting\Services\CoreAccounting\RecurringJournalEntryService;
use Modules\Admin\Filament\Resources\RecurringJournalEntries\RecurringJournalEntryResource;
use Modules\Admin\Models\Admin;
use Modules\Authorization\Models\Role;
use Tests\TestCase;

uses(TestCase::class, DatabaseMigrations::class);

beforeEach(function () {
    $this->tenant = Tenant::create();
    $this->tenant->domains()->create(['domain' => 'tenant1.localhost']);
    tenancy()->initialize($this->tenant);

    $this->assetType = AccountType::firstOrCreate(['type' => 'current_assets', 'account_group' => 'assets']);
    $this->expenseType = AccountType::firstOrCreate(['type' => 'operating_expenses', 'account_group' => 'expenses']);

    $this->cashAccount = Account::factory()->create([
        'name'            => 'Checking Account',
        'number'          => 1020,
        'account_type_id' => $this->assetType->id,
    ]);

    $this->rentExpenseAccount = Account::factory()->create([
        'name'            => 'Building Rent Expense',
        'number'          => 6010,
        'account_type_id' => $this->expenseType->id,
    ]);
});

afterEach(function () {
    if (tenancy()->initialized) {
        $tenant = tenancy()->tenant;
        tenancy()->end();
        $tenant->delete();
    }
});

test('calculateNextRunDate correctly computes date intervals across all frequencies', function () {
    $service = app(RecurringJournalEntryService::class);
    $baseDate = Carbon::parse('2026-01-15');

    expect($service->calculateNextRunDate('daily', $baseDate)->toDateString())->toBe('2026-01-16');
    expect($service->calculateNextRunDate('weekly', $baseDate)->toDateString())->toBe('2026-01-22');
    expect($service->calculateNextRunDate('monthly', $baseDate)->toDateString())->toBe('2026-02-15');
    expect($service->calculateNextRunDate('quarterly', $baseDate)->toDateString())->toBe('2026-04-15');
    expect($service->calculateNextRunDate('yearly', $baseDate)->toDateString())->toBe('2027-01-15');
});

test('generateReference dynamic pattern interpolation produces unique references', function () {
    $service = app(RecurringJournalEntryService::class);
    $date = Carbon::parse('2026-05-10');

    $ref1 = $service->generateReference('REC-RENT-{YYYY}-{MM}', $date, 1);
    expect($ref1)->toBe('REC-RENT-2026-05');

    $ref2 = $service->generateReference('ACCRUAL-{YYYY}-{MM}-{SEQ}', $date, 7);
    expect($ref2)->toBe('ACCRUAL-2026-05-007');
});

test('service batch processes due recurring entries, creates balanced journal entry, and updates schedule', function () {
    $service = app(RecurringJournalEntryService::class);

    $recurring = RecurringJournalEntry::create([
        'reference_template' => 'REC-RENT-{YYYY}-{MM}',
        'description'        => 'Monthly Corporate Office Rent',
        'frequency'          => 'monthly',
        'start_date'         => '2026-01-01',
        'next_run_date'      => '2026-01-01',
        'status'             => 'active',
        'currency_code'      => 'USD',
        'exchange_rate'      => 1.0,
        'auto_post'          => true,
        'total_debit'        => 2500.00,
        'total_credit'       => 2500.00,
    ]);

    RecurringJournalEntryLine::create([
        'recurring_journal_entry_id' => $recurring->id,
        'account_id'                 => $this->rentExpenseAccount->id,
        'debit'                      => 2500.00,
        'credit'                     => 0.00,
        'description'                => 'Office Rent Debit',
    ]);

    RecurringJournalEntryLine::create([
        'recurring_journal_entry_id' => $recurring->id,
        'account_id'                 => $this->cashAccount->id,
        'debit'                      => 0.00,
        'credit'                     => 2500.00,
        'description'                => 'Office Rent Cash Credit',
    ]);

    // Process due as of 2026-01-01
    $results = $service->processDueEntries(Carbon::parse('2026-01-01'));

    expect($results['processed'])->toBe(1);
    expect($results['succeeded'])->toBe(1);
    expect($results['failed'])->toBe(0);

    // Verify generated journal entry
    $createdEntry = JournalEntry::where('recurring_journal_entry_id', $recurring->id)->first();
    expect($createdEntry)->not->toBeNull();
    expect($createdEntry->reference)->toBe('REC-RENT-2026-01');
    expect((float) $createdEntry->total_debit)->toBe(2500.00);
    expect((float) $createdEntry->total_credit)->toBe(2500.00);
    expect($createdEntry->status)->toBe('approved');
    expect($createdEntry->lines)->toHaveCount(2);

    // Verify schedule was advanced
    $recurring->refresh();
    expect($recurring->last_run_date->toDateString())->toBe('2026-01-01');
    expect($recurring->next_run_date->toDateString())->toBe('2026-02-01');
    expect($recurring->status)->toBe('active');
});

test('recurring entry marks completed when next run date exceeds end_date', function () {
    $service = app(RecurringJournalEntryService::class);

    $recurring = RecurringJournalEntry::create([
        'reference_template' => 'SUB-SAAS-{YYYY}-{MM}',
        'description'        => 'Software license expiring in Jan',
        'frequency'          => 'monthly',
        'start_date'         => '2026-01-01',
        'next_run_date'      => '2026-01-01',
        'end_date'           => '2026-01-15', // Expires before Feb 01
        'status'             => 'active',
        'currency_code'      => 'USD',
        'auto_post'          => true,
        'total_debit'        => 100.00,
        'total_credit'       => 100.00,
    ]);

    RecurringJournalEntryLine::create([
        'recurring_journal_entry_id' => $recurring->id,
        'account_id'                 => $this->rentExpenseAccount->id,
        'debit'                      => 100.00,
        'credit'                     => 0.00,
    ]);
    RecurringJournalEntryLine::create([
        'recurring_journal_entry_id' => $recurring->id,
        'account_id'                 => $this->cashAccount->id,
        'debit'                      => 0.00,
        'credit'                     => 100.00,
    ]);

    $service->processDueEntries(Carbon::parse('2026-01-01'));

    $recurring->refresh();
    expect($recurring->status)->toBe('completed');
});

test('paused schedules are skipped by due runner and can be toggled', function () {
    $service = app(RecurringJournalEntryService::class);

    $recurring = RecurringJournalEntry::create([
        'reference_template' => 'PAUSED-EXP-{YYYY}-{MM}',
        'description'        => 'Paused expense',
        'frequency'          => 'monthly',
        'start_date'         => '2026-01-01',
        'next_run_date'      => '2026-01-01',
        'status'             => 'active',
        'currency_code'      => 'USD',
        'auto_post'          => true,
        'total_debit'        => 50.00,
        'total_credit'       => 50.00,
    ]);

    RecurringJournalEntryLine::create([
        'recurring_journal_entry_id' => $recurring->id,
        'account_id'                 => $this->rentExpenseAccount->id,
        'debit'                      => 50.00,
        'credit'                     => 0.00,
    ]);
    RecurringJournalEntryLine::create([
        'recurring_journal_entry_id' => $recurring->id,
        'account_id'                 => $this->cashAccount->id,
        'debit'                      => 0.00,
        'credit'                     => 50.00,
    ]);

    // Pause the entry
    $service->toggleStatus($recurring);
    expect($recurring->fresh()->status)->toBe('paused');

    // Run due processor
    $results = $service->processDueEntries(Carbon::parse('2026-01-01'));
    expect($results['processed'])->toBe(0);
    expect(JournalEntry::where('recurring_journal_entry_id', $recurring->id)->count())->toBe(0);

    // Resume the entry
    $service->toggleStatus($recurring);
    expect($recurring->fresh()->status)->toBe('active');
});

test('unbalanced recurring template throws DomainException', function () {
    $service = app(RecurringJournalEntryService::class);

    $recurring = RecurringJournalEntry::create([
        'reference_template' => 'UNBALANCED-{YYYY}',
        'description'        => 'Faulty template',
        'frequency'          => 'monthly',
        'start_date'         => '2026-01-01',
        'next_run_date'      => '2026-01-01',
        'status'             => 'active',
        'currency_code'      => 'USD',
        'auto_post'          => true,
        'total_debit'        => 500.00,
        'total_credit'       => 400.00,
    ]);

    RecurringJournalEntryLine::create([
        'recurring_journal_entry_id' => $recurring->id,
        'account_id'                 => $this->rentExpenseAccount->id,
        'debit'                      => 500.00,
        'credit'                     => 0.00,
    ]);
    RecurringJournalEntryLine::create([
        'recurring_journal_entry_id' => $recurring->id,
        'account_id'                 => $this->cashAccount->id,
        'debit'                      => 0.00,
        'credit'                     => 400.00, // Unbalanced!
    ]);

    $this->expectException(\DomainException::class);
    $service->postEntry($recurring);
});

test('artisan command accounting:process-recurring executes successfully in tenant context', function () {
    RecurringJournalEntry::create([
        'reference_template' => 'CMD-RENT-{YYYY}-{MM}',
        'description'        => 'Rent via Command',
        'frequency'          => 'monthly',
        'start_date'         => '2026-01-01',
        'next_run_date'      => '2026-01-01',
        'status'             => 'active',
        'currency_code'      => 'USD',
        'auto_post'          => true,
        'total_debit'        => 1200.00,
        'total_credit'       => 1200.00,
    ]);

    $entry = RecurringJournalEntry::first();
    RecurringJournalEntryLine::create([
        'recurring_journal_entry_id' => $entry->id,
        'account_id'                 => $this->rentExpenseAccount->id,
        'debit'                      => 1200.00,
        'credit'                     => 0.00,
    ]);
    RecurringJournalEntryLine::create([
        'recurring_journal_entry_id' => $entry->id,
        'account_id'                 => $this->cashAccount->id,
        'debit'                      => 0.00,
        'credit'                     => 1200.00,
    ]);

    $this->artisan('accounting:process-recurring', ['--date' => '2026-01-01'])
        ->assertSuccessful();

    expect(JournalEntry::where('recurring_journal_entry_id', $entry->id)->count())->toBe(1);
});

test('recurring journal entry resource enforces RBAC', function () {
    // 1. Super Admin has full access
    $superAdmin = Admin::factory()->create();
    $superAdmin->assignRole('super_admin');
    $this->actingAs($superAdmin, 'admin');
    expect(RecurringJournalEntryResource::canAccess())->toBeTrue();
    expect(RecurringJournalEntryResource::canCreate())->toBeTrue();

    // 2. Accountant can create entries
    $accountant = Admin::factory()->create();
    $roleAcc = Role::firstOrCreate(['name' => 'accountant', 'guard_name' => 'admin']);
    $accountant->assignRole($roleAcc);
    $this->actingAs($accountant, 'admin');
    expect(RecurringJournalEntryResource::canAccess())->toBeTrue();
    expect(RecurringJournalEntryResource::canCreate())->toBeTrue();

    // 3. Auditor is read-only and blocked from create/delete
    $auditor = Admin::factory()->create();
    $roleAud = Role::firstOrCreate(['name' => 'auditor', 'guard_name' => 'admin']);
    $auditor->assignRole($roleAud);
    $this->actingAs($auditor, 'admin');
    expect(RecurringJournalEntryResource::canAccess())->toBeFalse();
    expect(RecurringJournalEntryResource::canCreate())->toBeFalse();
});
