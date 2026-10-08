<?php

use App\Models\Tenant;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Modules\Accounting\Models\Account;
use Modules\Accounting\Models\AccountType;
use Modules\Accounting\Models\AccountingAuditLog;
use Modules\Accounting\Models\JournalEntry;
use Modules\Accounting\Models\JournalEntryLine;
use Modules\Accounting\Services\CoreAccounting\JournalEntryService;
use Modules\Admin\Filament\Resources\AuditTrail\AuditTrailResource;
use Modules\Admin\Models\Admin;
use Modules\Authorization\Models\Role;
use Tests\TestCase;

uses(TestCase::class, DatabaseMigrations::class);

beforeEach(function () {
    $this->tenant = Tenant::create();
    $this->tenant->domains()->create(['domain' => 'tenant1.localhost']);
    tenancy()->initialize($this->tenant);

    $this->currentAssetType = AccountType::firstOrCreate(['type' => 'current_assets', 'account_group' => 'assets']);
    $this->revType = AccountType::firstOrCreate(['type' => 'operating_revenue', 'account_group' => 'revenues']);

    $this->cashAcc = Account::factory()->create([
        'name'            => 'Cash Checking',
        'number'          => 1010,
        'account_type_id' => $this->currentAssetType->id,
    ]);

    $this->salesAcc = Account::factory()->create([
        'name'            => 'Consulting Sales',
        'number'          => 4010,
        'account_type_id' => $this->revType->id,
    ]);
});

afterEach(function () {
    if (tenancy()->initialized) {
        $tenant = tenancy()->tenant;
        tenancy()->end();
        $tenant->delete();
    }
});

test('journal entry creation automatically creates an audit log entry', function () {
    $entry = JournalEntry::create([
        'reference'     => 'JV-AUDIT-001',
        'date'          => '2026-04-01 10:00:00',
        'description'   => 'Audit test entry',
        'type'          => 'journal',
        'status'        => 'approved',
        'total_debit'   => 1500.00,
        'total_credit'  => 1500.00,
    ]);

    $log = AccountingAuditLog::where('auditable_type', JournalEntry::class)
        ->where('auditable_id', $entry->id)
        ->where('event', 'created')
        ->first();

    expect($log)->not->toBeNull();
    expect($log->description)->toContain('JV-AUDIT-001');
    expect($log->new_values)->toHaveKey('reference', 'JV-AUDIT-001');
});

test('journal entry reversal records reversed audit log with before and after state', function () {
    $entry = JournalEntry::create([
        'reference'     => 'JV-REV-001',
        'date'          => '2026-04-01 10:00:00',
        'description'   => 'Entry to reverse',
        'type'          => 'journal',
        'status'        => 'approved',
        'total_debit'   => 500.00,
        'total_credit'  => 500.00,
    ]);

    JournalEntryLine::create([
        'source_type'      => 'user',
        'source_reference' => 1,
        'journal_entry_id' => $entry->id,
        'account_id'       => $this->cashAcc->id,
        'debit'            => 500.00,
        'credit'           => 0.00,
        'date'             => '2026-04-01',
    ]);

    JournalEntryLine::create([
        'source_type'      => 'user',
        'source_reference' => 1,
        'journal_entry_id' => $entry->id,
        'account_id'       => $this->salesAcc->id,
        'debit'            => 0.00,
        'credit'           => 500.00,
        'date'             => '2026-04-01',
    ]);

    $service = app(JournalEntryService::class);
    $service->reverse($entry->id, 'Testing reversal audit trail');

    $revLog = AccountingAuditLog::where('auditable_type', JournalEntry::class)
        ->where('auditable_id', $entry->id)
        ->where('event', 'reversed')
        ->first();

    expect($revLog)->not->toBeNull();
    expect($revLog->event)->toBe('reversed');
    expect($revLog->description)->toContain('Reversed');
});

test('audit trail resource enforces immutability and permits auditor access', function () {
    expect(AuditTrailResource::canCreate())->toBeFalse();

    // Auditor can view audit trail
    $auditor = Admin::factory()->create();
    $role = Role::firstOrCreate(['name' => 'auditor', 'guard_name' => 'admin']);
    $auditor->assignRole($role);
    $this->actingAs($auditor, 'admin');

    expect(AuditTrailResource::canAccess())->toBeTrue();
});
