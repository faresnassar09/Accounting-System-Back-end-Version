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

    $this->types = [
        'assets' => AccountType::where('type', 'current_assets')->first()->id,
        'liabilities' => AccountType::where('type', 'current_liabilities')->first()->id,
        'equity' => AccountType::where('type', 'retained_earnings')->first()->id,
        'revenue' => AccountType::where('type', 'operating_revenue')->first()->id,
        'expenses' => AccountType::where('type', 'operating_expenses')->first()->id,
    ];
});

test('balance sheet matches the basic accounting equation', function () {
    $cash = Account::factory()->create(['name' => 'Cash', 'account_type_id' => $this->types['assets']]);
    $loan = Account::factory()->create(['name' => 'Loan', 'account_type_id' => $this->types['liabilities']]);

    $entry = JournalEntry::factory()->create(['date' => '2026-01-01', 'total_debit' => 5000, 'total_credit' => 5000]);
    
    JournalEntryLine::factory()->create(['journal_entry_id' => $entry->id, 'account_id' => $cash->id, 'debit' => 5000]);
    JournalEntryLine::factory()->create(['journal_entry_id' => $entry->id, 'account_id' => $loan->id, 'credit' => 5000]);

    $response = $this->getJson('api/v1/accounting/reports/balance-sheet?endDate=2026-01-31');

    $data = $response->json('data');
    $assets = $data['assets_group']['group_total'];
    $liabilities = $data['liabilities_and_equity_group']['sub_types']['liabilities_group']['type_total'];

    expect($assets)->toBe(5000);
    expect($liabilities)->toBe(5000);
    expect($assets)->toBe($liabilities);
});

test('balance sheet includes net profit from revenue and expenses', function () {
    $cash = Account::factory()->create(['name' => 'Cash', 'account_type_id' => $this->types['assets']]);
    $sales = Account::factory()->create(['name' => 'Sales', 'account_type_id' => $this->types['revenue']]);
    $rent = Account::factory()->create(['name' => 'Rent', 'account_type_id' => $this->types['expenses']]);

    $entry1 = JournalEntry::factory()->create(['date' => '2026-01-05', 'total_debit' => 1000, 'total_credit' => 1000]);
    JournalEntryLine::factory()->create(['journal_entry_id' => $entry1->id, 'account_id' => $cash->id, 'debit' => 1000]);
    JournalEntryLine::factory()->create(['journal_entry_id' => $entry1->id, 'account_id' => $sales->id, 'credit' => 1000]);

    $entry2 = JournalEntry::factory()->create(['date' => '2026-01-10', 'total_debit' => 400, 'total_credit' => 400]);
    JournalEntryLine::factory()->create(['journal_entry_id' => $entry2->id, 'account_id' => $rent->id, 'debit' => 400]);
    JournalEntryLine::factory()->create(['journal_entry_id' => $entry2->id, 'account_id' => $cash->id, 'credit' => 400]);

    $response = $this->getjson('api/v1/accounting/reports/balance-sheet?endDate=2026-01-31');
    
    $data = $response->json('data');
    $assets = $data['assets_group']['group_total'];
    $netProfit = $data['liabilities_and_equity_group']['sub_types']['equity_group']['accounts'][0]['netBalance'] * -1;

    expect($assets)->toBe(600);
    expect($netProfit)->toBe(600);
    expect($assets)->toBe($netProfit);
});

test('balance sheet ignores transactions after the end date', function () {
    $cash = Account::factory()->create(['account_type_id' => $this->types['assets']]);
    $otherAccount = Account::factory()->create(); 
    
    $entry1 = JournalEntry::factory()->create(['date' => '2026-01-15', 'total_debit' => 100, 'total_credit' => 100]);
    JournalEntryLine::factory()->create([
        'journal_entry_id' => $entry1->id, 
        'account_id' => $cash->id, 
        'debit' => 100
    ]);
    JournalEntryLine::factory()->create([
        'journal_entry_id' => $entry1->id, 
        'account_id' => $otherAccount->id, 
        'credit' => 100
    ]);

    $entry2 = JournalEntry::factory()->create(['date' => '2026-02-15', 'total_debit' => 500, 'total_credit' => 500]);
    JournalEntryLine::factory()->create([
        'journal_entry_id' => $entry2->id, 
        'account_id' => $cash->id, 
        'debit' => 500
    ]);
    JournalEntryLine::factory()->create([
        'journal_entry_id' => $entry2->id, 
        'account_id' => $otherAccount->id, 
        'credit' => 500
    ]);

    $response = $this->getJson('api/v1/accounting/reports/balance-sheet?endDate=2026-02-13');
    
    expect($response->json('data.assets_group.group_total'))->toBe(100);
});

afterEach(function () {
    if (tenancy()->initialized) {
        $tenant = tenancy()->tenant;
        tenancy()->end();
        $tenant->delete();
    }
});