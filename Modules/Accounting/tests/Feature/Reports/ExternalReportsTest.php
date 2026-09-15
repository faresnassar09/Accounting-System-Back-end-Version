<?php

use App\Models\Tenant;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Passport\Passport;
use Modules\Accounting\Models\Account;
use Modules\Accounting\Models\AccountType;
use Modules\Accounting\Models\JournalEntry;
use Modules\Accounting\Models\JournalEntryLine;
use Modules\Authorization\Models\Role;
use Modules\User\Models\User;
use Tests\TestCase;

uses(TestCase::class, DatabaseMigrations::class);

beforeEach(function () {
    $this->tenant = Tenant::create();
    $this->tenant->domains()->create(['domain' => 'tenant1.localhost']);
    tenancy()->initialize($this->tenant);

    $clientRepository = app(\Laravel\Passport\ClientRepository::class);
    $this->client = $clientRepository->createClientCredentialsGrantClient('External Service Client');

    Passport::actingAsClient($this->client);

    $this->cashAccount = Account::factory()->create([
        'name' => 'Cash',
        'number' => 1010,
        'account_type_id' => AccountType::where('type', 'current_assets')->first()->id,
    ]);

    $this->revenueAccount = Account::factory()->create([
        'name' => 'Sales Revenue',
        'number' => 4010,
        'account_type_id' => AccountType::where('type', 'gross_sales')->first()->id,
    ]);

    $this->expenseAccount = Account::factory()->create([
        'name' => 'Rent Expense',
        'number' => 6010,
        'account_type_id' => AccountType::where('type', 'operating_expenses')->first()->id,
    ]);

    // Create a transaction: Cash 1000, Revenue 1000
    $entry1 = JournalEntry::factory()->create([
        'date' => '2026-02-15',
        'type' => 'journal',
        'total_debit' => 1000,
        'total_credit' => 1000,
    ]);

    JournalEntryLine::factory()->create([
        'journal_entry_id' => $entry1->id,
        'account_id' => $this->cashAccount->id,
        'debit' => 1000,
        'credit' => 0,
    ]);

    JournalEntryLine::factory()->create([
        'journal_entry_id' => $entry1->id,
        'account_id' => $this->revenueAccount->id,
        'debit' => 0,
        'credit' => 1000,
    ]);
});

test('external trial balance report returns successful response', function () {
    $response = $this->getJson('/api/external/reports/trial-balance?endDate=2026-12-31');

    $response->assertStatus(200);
    $response->assertJsonPath('data.totals.total_debit', 1000);
    $response->assertJsonPath('data.totals.total_credit', 1000);
    $response->assertJsonPath('data.totals.isBalanced', true);
});

test('external general ledger report returns successful response with accountId and accountNumber', function () {
    // Test with accountId
    $response = $this->getJson("/api/external/reports/general-ledger?accountId={$this->cashAccount->id}&startDate=2026-01-01&endDate=2026-12-31");
    $response->assertStatus(200);
    $response->assertJsonPath('data.closing_balance', 1000);

    // Test with account_number (3rd party friendly)
    $response2 = $this->getJson("/api/external/reports/general-ledger?account_number=1010&start_date=2026-01-01&end_date=2026-12-31");
    $response2->assertStatus(200);
    $response2->assertJsonPath('data.closing_balance', 1000);
});

test('external income statement report returns successful response', function () {
    $response = $this->getJson('/api/external/reports/income-statement?startDate=2026-01-01&endDate=2026-12-31');

    $response->assertStatus(200);
    $response->assertJsonPath('data.revenues.total_revenue', 1000);
    $response->assertJsonPath('data.final_result.net_income', 1000);
});

test('external balance sheet report returns successful response', function () {
    $response = $this->getJson('/api/external/reports/balance-sheet?endDate=2026-12-31');

    $response->assertStatus(200);
    $response->assertJsonStructure([
        'data' => [
            'assets_group' => ['group_code', 'group_name', 'group_total', 'sub_types'],
            'liabilities_and_equity_group' => ['group_code', 'group_name', 'group_total', 'sub_types'],
        ]
    ]);
});

afterEach(function () {
    if (tenancy()->initialized) {
        $tenant = tenancy()->tenant;
        tenancy()->end();
        $tenant->delete();
    }
});
