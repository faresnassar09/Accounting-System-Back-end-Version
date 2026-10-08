<?php

use App\Models\Tenant;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Modules\Accounting\Models\Account;
use Modules\Accounting\Models\AccountType;
use Modules\Accounting\Models\JournalEntry;
use Modules\Accounting\Models\JournalEntryLine;
use Modules\Accounting\Services\CoreAccounting\BankReconciliationService;
use Modules\Admin\Filament\Resources\BankReconciliations\BankStatementResource;
use Modules\Admin\Models\Admin;
use Tests\TestCase;

uses(TestCase::class, DatabaseMigrations::class);

beforeEach(function () {
    $this->tenant = Tenant::create();
    $this->tenant->domains()->create(['domain' => 'tenant1.localhost']);
    tenancy()->initialize($this->tenant);

    $this->currentAssetType = AccountType::firstOrCreate(['type' => 'current_assets', 'account_group' => 'assets']);
    $this->revType = AccountType::firstOrCreate(['type' => 'operating_revenue', 'account_group' => 'revenues']);
    $this->expType = AccountType::firstOrCreate(['type' => 'operating_expenses', 'account_group' => 'expenses']);

    $this->bankAccount = Account::factory()->create([
        'name'            => 'Chase Operating Bank',
        'number'          => 1015,
        'account_type_id' => $this->currentAssetType->id,
    ]);

    $this->revenueAccount = Account::factory()->create([
        'name'            => 'Consulting Revenue',
        'number'          => 4020,
        'account_type_id' => $this->revType->id,
    ]);

    $this->bankFeeAccount = Account::factory()->create([
        'name'            => 'Bank Service Charges',
        'number'          => 6050,
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

test('bank statement imports and creates lines with initial unmatched status', function () {
    $service = app(BankReconciliationService::class);

    $rows = [
        ['date' => '2026-03-01', 'description' => 'Client Wire Transfer', 'reference' => 'WIRE-991', 'amount' => 5000.00],
        ['date' => '2026-03-02', 'description' => 'Monthly Account Service Fee', 'reference' => 'FEE-01', 'amount' => -25.00],
    ];

    $statement = $service->importStatement(
        $this->bankAccount->id,
        '2026-03-31',
        1000.00,
        5975.00,
        $rows,
        'statement_mar2026.csv'
    );

    expect($statement)->not->toBeNull();
    expect($statement->lines)->toHaveCount(2);
    expect($statement->status)->toBe('draft');
    expect((float) $statement->opening_balance)->toBe(1000.00);
    expect((float) $statement->closing_balance)->toBe(5975.00);
});

test('autoMatch pairs matching ledger journal lines with bank statement lines', function () {
    $service = app(BankReconciliationService::class);

    // Create an actual ledger journal entry: Deposit of $5,000
    $entry = JournalEntry::create([
        'reference'     => 'JV-DEP-001',
        'date'          => '2026-03-02 10:00:00',
        'description'   => 'Customer payment deposited',
        'type'          => 'journal',
        'status'        => 'approved',
        'total_debit'   => 5000.00,
        'total_credit'  => 5000.00,
    ]);

    $bankLine = JournalEntryLine::create([
        'source_type'      => 'user',
        'source_reference' => 1,
        'journal_entry_id' => $entry->id,
        'account_id'       => $this->bankAccount->id,
        'debit'            => 5000.00,
        'credit'           => 0.00,
        'date'             => '2026-03-02',
        'is_reconciled'    => false,
    ]);

    JournalEntryLine::create([
        'source_type'      => 'user',
        'source_reference' => 1,
        'journal_entry_id' => $entry->id,
        'account_id'       => $this->revenueAccount->id,
        'debit'            => 0.00,
        'credit'           => 5000.00,
        'date'             => '2026-03-02',
        'is_reconciled'    => false,
    ]);

    $rows = [
        ['date' => '2026-03-01', 'description' => 'Wire deposit from customer', 'amount' => 5000.00],
    ];

    $statement = $service->importStatement(
        $this->bankAccount->id,
        '2026-03-31',
        0.00,
        5000.00,
        $rows
    );

    $matched = $service->autoMatch($statement);
    expect($matched)->toBe(1);

    $sLine = $statement->lines()->first();
    expect($sLine->status)->toBe('matched');
    expect($sLine->matched_journal_entry_line_id)->toBe($bankLine->id);

    $bankLine->refresh();
    expect($bankLine->is_reconciled)->toBeTrue();
    expect($bankLine->reconciled_at)->not->toBeNull();
});

test('createAdjustmentEntry generates balancing entry for bank fee and reconciles line', function () {
    $service = app(BankReconciliationService::class);

    $rows = [
        ['date' => '2026-03-31', 'description' => 'Monthly wire maintenance fee', 'amount' => -45.00],
    ];

    $statement = $service->importStatement(
        $this->bankAccount->id,
        '2026-03-31',
        1000.00,
        955.00,
        $rows
    );

    $sLine = $statement->lines()->first();

    $adjEntry = $service->createAdjustmentEntry(
        $sLine->id,
        $this->bankFeeAccount->id,
        'Monthly bank service charge'
    );

    expect($adjEntry)->not->toBeNull();
    expect((float) $adjEntry->total_debit)->toBe(45.00);
    expect((float) $adjEntry->total_credit)->toBe(45.00);

    $sLine->refresh();
    expect($sLine->status)->toBe('created_adjustment');
    expect($sLine->matched_journal_entry_line_id)->not->toBeNull();
});

test('finalizeReconciliation succeeds when discrepancy is zero and fails otherwise', function () {
    $service = app(BankReconciliationService::class);

    $rows = [
        ['date' => '2026-03-31', 'description' => 'Opening matching test', 'amount' => 500.00],
    ];

    // Target closing 1500, opening 1000 => requires +500 matched
    $statement = $service->importStatement(
        $this->bankAccount->id,
        '2026-03-31',
        1000.00,
        1500.00,
        $rows
    );

    // Unmatched: Discrepancy is 500 => Finalize must fail
    expect(fn () => $service->finalizeReconciliation($statement))
        ->toThrow(\DomainException::class);

    // Manually mark line as matched
    $sLine = $statement->lines()->first();
    $sLine->update(['status' => 'matched']);

    // Now calculated balance is 1000 + 500 = 1500, discrepancy is 0.00
    expect($statement->discrepancy)->toBe(0.0);
    expect($service->finalizeReconciliation($statement))->toBeTrue();
    expect($statement->fresh()->status)->toBe('reconciled');
});

test('bank statement resource can be accessed by authorized admins', function () {
    $admin = Admin::factory()->create();
    $admin->assignRole('super_admin');
    $this->actingAs($admin, 'admin');

    expect(BankStatementResource::canAccess())->toBeTrue();
});
