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

    $this->user = User::factory()->create();
    $role = Role::create(['name' => 'accountant', 'guard_name' => 'web']);
    $this->user->assignRole($role);
    Passport::actingAs($this->user);

    $this->currAssetType = AccountType::firstOrCreate(['type' => 'current_assets', 'account_group' => 'assets']);
    $this->expType = AccountType::firstOrCreate(['type' => 'operating_expenses', 'account_group' => 'expenses']);
    $this->revType = AccountType::firstOrCreate(['type' => 'operating_revenue', 'account_group' => 'revenues']);
    $this->accumDepType = AccountType::firstOrCreate(['type' => 'accumulated_depreciation', 'account_group' => 'assets']);

    $this->cashAcc = Account::factory()->create(['name' => 'Main Bank', 'number' => '1010', 'account_type_id' => $this->currAssetType->id]);
    $this->expenseAcc = Account::factory()->create(['name' => 'Office Rent', 'number' => '6010', 'account_type_id' => $this->expType->id]);
    $this->depExpenseAcc = Account::factory()->create(['name' => 'Depreciation Expense', 'number' => '6020', 'account_type_id' => $this->expType->id]);
    $this->accumDepAcc = Account::factory()->create(['name' => 'Accumulated Dep', 'number' => '1590', 'account_type_id' => $this->accumDepType->id]);
});

afterEach(function () {
    if (tenancy()->initialized) {
        $tenant = tenancy()->tenant;
        tenancy()->end();
        $tenant->delete();
    }
});

test('user can create, update, and delete accounts in chart of accounts', function () {
    // 1. Get Account Types
    $typesRes = $this->getJson('/api/v1/accounting/account-types');
    $typesRes->assertStatus(200)
        ->assertJsonPath('success', true)
        ->assertJsonStructure([
            'data' => [
                '*' => ['id', 'type', 'name', 'account_group']
            ]
        ]);

    // 2. Create sub-account
    $res = $this->postJson('/api/v1/accounting/accounts', [
        'name'            => 'Petty Cash Branch A',
        'number'          => '1010-01',
        'parent_id'       => $this->cashAcc->id,
        'account_type_id' => $this->currAssetType->id,
        'description'     => 'Local cash for minor items',
    ]);
    $res->assertStatus(201)
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.name', 'Petty Cash Branch A');

    $accountId = $res->json('data.id');

    // 3. Update sub-account
    $updateRes = $this->putJson("/api/v1/accounting/accounts/{$accountId}", [
        'name'        => 'Petty Cash HQ',
        'number'      => '1010-01',
        'description' => 'Updated HQ cash',
    ]);
    $updateRes->assertStatus(200)
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.name', 'Petty Cash HQ');

    // 4. Delete account
    $delRes = $this->deleteJson("/api/v1/accounting/accounts/{$accountId}");
    $delRes->assertStatus(200)->assertJsonPath('success', true);
});

test('user can manage budgets and view budget vs actual report', function () {
    // 1. Create Budget
    $res = $this->postJson('/api/v1/accounting/budgets', [
        'fiscal_year'      => 2026,
        'account_id'       => $this->expenseAcc->id,
        'allocated_amount' => 12000,
        'notes'            => 'Annual rent budget',
    ]);
    $res->assertStatus(201)
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.allocated_amount', '12000.00');

    $budgetId = $res->json('data.id');

    // 2. List budgets
    $listRes = $this->getJson('/api/v1/accounting/budgets?fiscal_year=2026');
    $listRes->assertStatus(200)
        ->assertJsonPath('success', true)
        ->assertJsonCount(1, 'data');

    // 3. Post an expense entry
    $entry = JournalEntry::create([
        'reference' => 'JE-RENT-01', 'date' => '2026-03-01', 'description' => 'March Rent', 'total_debit' => 1000, 'total_credit' => 1000, 'type' => 'journal', 'status' => 'approved',
    ]);
    JournalEntryLine::create(['journal_entry_id' => $entry->id, 'account_id' => $this->expenseAcc->id, 'debit' => 1000, 'credit' => 0, 'date' => '2026-03-01', 'source_type' => 'user', 'source_reference' => (string)$this->user->id]);
    JournalEntryLine::create(['journal_entry_id' => $entry->id, 'account_id' => $this->cashAcc->id, 'debit' => 0, 'credit' => 1000, 'date' => '2026-03-01', 'source_type' => 'user', 'source_reference' => (string)$this->user->id]);

    // 4. Query Budget vs Actual
    $bvaRes = $this->getJson('/api/v1/accounting/reports/budget-vs-actual?fiscal_year=2026');
    $bvaRes->assertStatus(200)
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.total_budgeted', 12000)
        ->assertJsonPath('data.total_actual', 1000);
});

test('user can create recurring journal entries and execute them', function () {
    $res = $this->postJson('/api/v1/accounting/recurring-entries', [
        'reference_template' => 'REC-RENT-{YYYY}-{MM}',
        'description'        => 'Monthly Rent Schedule',
        'frequency'          => 'monthly',
        'start_date'         => '2026-01-01',
        'auto_post'          => true,
        'lines'              => [
            ['account_id' => $this->expenseAcc->id, 'debit' => 1500, 'credit' => 0, 'description' => 'Rent'],
            ['account_id' => $this->cashAcc->id, 'debit' => 0, 'credit' => 1500, 'description' => 'Bank'],
        ],
    ]);
    $res->assertStatus(201)
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.total_debit', '1500.00');

    $recurringId = $res->json('data.id');

    // Trigger run
    $runRes = $this->postJson("/api/v1/accounting/recurring-entries/{$recurringId}/run");
    $runRes->assertStatus(200)
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.journal_entry.total_debit', 1500);
});

test('user can register fixed asset and trigger monthly depreciation', function () {
    $res = $this->postJson('/api/v1/accounting/fixed-assets', [
        'asset_number'                       => 'FA-LAPTOP-001',
        'name'                               => 'MacBook Pro 16',
        'category'                           => 'equipment',
        'purchase_date'                      => '2026-01-01',
        'purchase_cost'                      => 2400,
        'salvage_value'                      => 0,
        'useful_life_months'                 => 24,
        'depreciation_method'                => 'straight_line',
        'asset_account_id'                   => $this->cashAcc->id,
        'depreciation_expense_account_id'    => $this->depExpenseAcc->id,
        'accumulated_depreciation_account_id'=> $this->accumDepAcc->id,
    ]);
    $res->assertStatus(201)
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.book_value', '2400.00');

    $assetId = $res->json('data.id');

    // Run depreciation for January 2026
    $depRes = $this->postJson("/api/v1/accounting/fixed-assets/{$assetId}/depreciate", [
        'period_date' => '2026-01-01',
    ]);
    $depRes->assertStatus(200)
        ->assertJsonPath('success', true);
});

test('user can import bank statement, query unreconciled lines, and auto-match', function () {
    // 1. Post a ledger transaction
    $entry = JournalEntry::create([
        'reference' => 'JE-DEP-01', 'date' => '2026-04-10', 'description' => 'Invoice Payment', 'total_debit' => 2000, 'total_credit' => 2000, 'type' => 'journal', 'status' => 'approved',
    ]);
    JournalEntryLine::create(['journal_entry_id' => $entry->id, 'account_id' => $this->cashAcc->id, 'debit' => 2000, 'credit' => 0, 'date' => '2026-04-10', 'source_type' => 'user', 'source_reference' => (string)$this->user->id]);
    JournalEntryLine::create(['journal_entry_id' => $entry->id, 'account_id' => $this->revType->id, 'debit' => 0, 'credit' => 2000, 'date' => '2026-04-10', 'source_type' => 'user', 'source_reference' => (string)$this->user->id]);

    // 2. Query unreconciled ledger lines
    $unrecRes = $this->getJson("/api/v1/accounting/bank-statements/unreconciled-lines?account_id={$this->cashAcc->id}");
    $unrecRes->assertStatus(200)
        ->assertJsonPath('success', true)
        ->assertJsonCount(1, 'data');

    // 3. Import bank statement
    $stmtRes = $this->postJson('/api/v1/accounting/bank-statements', [
        'account_id'      => $this->cashAcc->id,
        'statement_date'  => '2026-04-30',
        'opening_balance' => 0,
        'closing_balance' => 2000,
        'rows'            => [
            ['date' => '2026-04-10', 'description' => 'Customer Deposit', 'amount' => 2000],
        ],
    ]);
    $stmtRes->assertStatus(201)
        ->assertJsonPath('success', true);

    $statementId = $stmtRes->json('data.id');

    // 4. Auto-match
    $matchRes = $this->postJson("/api/v1/accounting/bank-statements/{$statementId}/auto-match");
    $matchRes->assertStatus(200)
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.matched_count', 1);
});
