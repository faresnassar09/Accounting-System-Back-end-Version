<?php

use App\Models\Tenant;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Modules\Accounting\Models\Account;
use Modules\Accounting\Models\AccountType;
use Modules\Accounting\Models\JournalEntry;
use Modules\Accounting\Models\JournalEntryLine;
use Modules\User\Models\User;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

uses(TestCase::class, DatabaseMigrations::class);

beforeEach(function () {
    $this->tenant = Tenant::create();
    $this->tenant->domains()->create(['domain' => 'tenant1.app.test']);
    tenancy()->initialize($this->tenant);

    $this->user = User::factory()->create();
    $role = Role::create(['name' => 'accountant']);
    $this->user->assignRole($role);
    $this->actingAs($this->user, 'sanctum');

    $this->cashAccount = Account::factory()->create([
        'name' => 'Cash',
        'account_type_id' => AccountType::where('type', 'current_assets')->first()->id
    ]);

    $this->expenseAccount = Account::factory()->create([
        'name' => 'Operating Expenses',
        'account_type_id' => AccountType::where('type', 'operating_expenses')->first()->id
    ]);
});

test('trial balance debit and credit are balanced', function () {
    $entry = JournalEntry::factory()->create([
        'user_id' => $this->user->id,
        'date' => '2026-01-20',
        'total_debit' => 3800,
        'total_credit' => 3800
    ]);

    JournalEntryLine::factory()->create([
        'account_id' => $this->cashAccount->id,
        'journal_entry_id' => $entry->id,
        'debit' => 3800,
        'credit' => 0
    ]);

    JournalEntryLine::factory()->create([
        'account_id' => $this->expenseAccount->id,
        'journal_entry_id' => $entry->id,
        'debit' => 0,
        'credit' => 3800
    ]);

    $response = $this->getJson('api/v1/accounting/reports/trial-balance?endDate=2026-01-25');
    
    $response->assertStatus(200);
    $reportData = $response->json('data.totals');

    expect($reportData['total_debit'])->toBe(3800);
    expect($reportData['total_credit'])->toBe(3800);
    expect($reportData['isBalanced'])->toBeTrue();
});

test('trial balance ignores entries after the specified end date', function () {

    $entryIn = JournalEntry::factory()->create(['date' => '2026-01-10', 'total_debit' => 1000, 'total_credit' => 1000]);
    JournalEntryLine::factory()->create([
        'account_id' => $this->cashAccount->id,
        'journal_entry_id' => $entryIn->id,
        'debit' => 1000,
        'credit' => 0
    ]);
    JournalEntryLine::factory()->create([
        'account_id' => $this->expenseAccount->id,
        'journal_entry_id' => $entryIn->id,
        'debit' => 0,
        'credit' => 1000
    ]);

    $entryOut = JournalEntry::factory()->create(['date' => '2026-02-10', 'total_debit' => 5000, 'total_credit' => 5000]);
    JournalEntryLine::factory()->create([
        'account_id' => $this->cashAccount->id,
        'journal_entry_id' => $entryOut->id,
        'debit' => 5000,
        'credit' => 0
    ]);
    JournalEntryLine::factory()->create([
        'account_id' => $this->expenseAccount->id,
        'journal_entry_id' => $entryOut->id,
        'debit' => 0,
        'credit' => 5000
    ]);

    $response = $this->getJson('api/v1/accounting/reports/trial-balance?endDate=2026-01-31');
    
    $response->assertStatus(200);
    expect($response->json('data.totals.total_debit'))->toBe(1000);
});

test('trial balance returns zero totals when no entries exist', function () {

    $response = $this->getJson('api/v1/accounting/reports/trial-balance?endDate=2020-01-01');
    
    $response->assertStatus(200);
    $totals = $response->json('data.totals');
    expect($totals['total_debit'])->toBe(0);
    expect($totals['isBalanced'])->toBeTrue();
});

afterEach(function () {
    if (tenancy()->initialized) {
        $tenant = tenancy()->tenant;
        tenancy()->end();
        $tenant->delete();
    }
});