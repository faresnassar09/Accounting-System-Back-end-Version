<?php

use App\Models\Tenant;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Passport\Passport;
use Modules\Accounting\Models\Account;
use Modules\Accounting\Models\AccountType;
use Modules\Accounting\Models\JournalEntry;
use Modules\Accounting\Models\JournalEntryLine;
use Modules\Authorization\Models\Role;
use Modules\Branch\Models\Branch;
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

    $this->branchA = Branch::create([
        'name' => 'Cairo Branch',
        'code' => 'CAI',
        'phone' => '0101111111',
        'address' => 'Cairo, Egypt',
        'active' => 1,
    ]);

    $this->branchB = Branch::create([
        'name' => 'Alexandria Branch',
        'code' => 'ALX',
        'phone' => '0102222222',
        'address' => 'Alexandria, Egypt',
        'active' => 1,
    ]);

    $this->cashType = AccountType::where('type', 'current_assets')->first();
    $this->revenueType = AccountType::where('type', 'operating_revenue')->first();
    $this->expenseType = AccountType::where('type', 'operating_expenses')->first();
    $this->equityType = AccountType::where('type', 'retained_earnings')->first();

    $this->cashAccount = Account::factory()->create([
        'name' => 'Cash in Hand',
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

test('user can create a journal entry tagged with a branch_id and lines inherit branch_id', function () {
    $data = [
        'journalHeader' => [
            'reference' => 'JE-BRANCH-01',
            'date' => '2026-04-01',
            'description' => 'Branch tagged transaction',
            'branch_id' => $this->branchA->id,
            'total_debit' => 5000,
            'total_credit' => 5000,
        ],
        'lines' => [
            [
                'account_id' => $this->cashAccount->id,
                'debit' => 5000,
                'credit' => 0,
            ],
            [
                'account_id' => $this->salesAccount->id,
                'debit' => 0,
                'credit' => 5000,
            ],
        ],
    ];

    $response = $this->postJson('api/v1/accounting/journal-entries', $data, [
        'Accept' => 'application/json',
    ]);

    $response->assertStatus(201);

    $this->assertDatabaseHas('journal_entries', [
        'reference' => 'JE-BRANCH-01',
        'branch_id' => $this->branchA->id,
    ]);

    $this->assertDatabaseHas('journal_entry_lines', [
        'account_id' => $this->cashAccount->id,
        'branch_id' => $this->branchA->id,
        'debit' => 5000,
    ]);

    $this->assertDatabaseHas('journal_entry_lines', [
        'account_id' => $this->salesAccount->id,
        'branch_id' => $this->branchA->id,
        'credit' => 5000,
    ]);
});

test('journal entries list filters correctly by branch_id', function () {
    $entryA = JournalEntry::factory()->create([
        'reference' => 'REF-CAIRO',
        'branch_id' => $this->branchA->id,
        'date' => '2026-04-01',
        'total_debit' => 1000,
        'total_credit' => 1000,
    ]);
    JournalEntryLine::factory()->create(['journal_entry_id' => $entryA->id, 'account_id' => $this->cashAccount->id, 'branch_id' => $this->branchA->id, 'debit' => 1000]);
    JournalEntryLine::factory()->create(['journal_entry_id' => $entryA->id, 'account_id' => $this->salesAccount->id, 'branch_id' => $this->branchA->id, 'credit' => 1000]);

    $entryB = JournalEntry::factory()->create([
        'reference' => 'REF-ALEX',
        'branch_id' => $this->branchB->id,
        'date' => '2026-04-02',
        'total_debit' => 2000,
        'total_credit' => 2000,
    ]);
    JournalEntryLine::factory()->create(['journal_entry_id' => $entryB->id, 'account_id' => $this->cashAccount->id, 'branch_id' => $this->branchB->id, 'debit' => 2000]);
    JournalEntryLine::factory()->create(['journal_entry_id' => $entryB->id, 'account_id' => $this->salesAccount->id, 'branch_id' => $this->branchB->id, 'credit' => 2000]);

    // Filter by Branch A
    $respA = $this->getJson("/api/v1/accounting/journal-entries?branch_id={$this->branchA->id}");
    $respA->assertStatus(200);
    $dataA = $respA->json('data.data');
    expect(collect($dataA)->pluck('reference')->toArray())->toContain('REF-CAIRO');
    expect(collect($dataA)->pluck('reference')->toArray())->not->toContain('REF-ALEX');

    // Filter by Branch B
    $respB = $this->getJson("/api/v1/accounting/journal-entries?branch_id={$this->branchB->id}");
    $respB->assertStatus(200);
    $dataB = $respB->json('data.data');
    expect(collect($dataB)->pluck('reference')->toArray())->toContain('REF-ALEX');
    expect(collect($dataB)->pluck('reference')->toArray())->not->toContain('REF-CAIRO');
});

test('trial balance isolates debit and credit by branch_id', function () {
    // Branch A: Cash 1000 Dr, Capital 1000 Cr
    $entryA = JournalEntry::factory()->create(['branch_id' => $this->branchA->id, 'date' => '2026-04-01', 'total_debit' => 1000, 'total_credit' => 1000]);
    JournalEntryLine::factory()->create(['journal_entry_id' => $entryA->id, 'branch_id' => $this->branchA->id, 'account_id' => $this->cashAccount->id, 'debit' => 1000, 'credit' => 0]);
    JournalEntryLine::factory()->create(['journal_entry_id' => $entryA->id, 'branch_id' => $this->branchA->id, 'account_id' => $this->capitalAccount->id, 'debit' => 0, 'credit' => 1000]);

    // Branch B: Cash 2500 Dr, Capital 2500 Cr
    $entryB = JournalEntry::factory()->create(['branch_id' => $this->branchB->id, 'date' => '2026-04-02', 'total_debit' => 2500, 'total_credit' => 2500]);
    JournalEntryLine::factory()->create(['journal_entry_id' => $entryB->id, 'branch_id' => $this->branchB->id, 'account_id' => $this->cashAccount->id, 'debit' => 2500, 'credit' => 0]);
    JournalEntryLine::factory()->create(['journal_entry_id' => $entryB->id, 'branch_id' => $this->branchB->id, 'account_id' => $this->capitalAccount->id, 'debit' => 0, 'credit' => 2500]);

    // Query Branch A only
    $responseA = $this->getJson("/api/v1/accounting/reports/trial-balance?branch_id={$this->branchA->id}&startDate=2026-04-01&endDate=2026-04-30");
    $responseA->assertStatus(200);
    $dataA = $responseA->json('data.totals');
    expect($dataA['total_debit'])->toEqual(1000);
    expect($dataA['total_credit'])->toEqual(1000);

    // Query Branch B only
    $responseB = $this->getJson("/api/v1/accounting/reports/trial-balance?branch_id={$this->branchB->id}&startDate=2026-04-01&endDate=2026-04-30");
    $responseB->assertStatus(200);
    $dataB = $responseB->json('data.totals');
    expect($dataB['total_debit'])->toEqual(2500);
    expect($dataB['total_credit'])->toEqual(2500);

    // Query All branches (unfiltered)
    $responseAll = $this->getJson("/api/v1/accounting/reports/trial-balance?startDate=2026-04-01&endDate=2026-04-30");
    $responseAll->assertStatus(200);
    $dataAll = $responseAll->json('data.totals');
    expect($dataAll['total_debit'])->toEqual(3500);
    expect($dataAll['total_credit'])->toEqual(3500);
});

test('income statement calculates net profit isolated by branch_id', function () {
    // Branch A: Revenue 10000, Expense 4000 => Net Income 6000
    $revA = JournalEntry::factory()->create(['branch_id' => $this->branchA->id, 'date' => '2026-04-05', 'total_debit' => 10000, 'total_credit' => 10000]);
    JournalEntryLine::factory()->create(['journal_entry_id' => $revA->id, 'branch_id' => $this->branchA->id, 'account_id' => $this->cashAccount->id, 'debit' => 10000]);
    JournalEntryLine::factory()->create(['journal_entry_id' => $revA->id, 'branch_id' => $this->branchA->id, 'account_id' => $this->salesAccount->id, 'credit' => 10000]);

    $expA = JournalEntry::factory()->create(['branch_id' => $this->branchA->id, 'date' => '2026-04-06', 'total_debit' => 4000, 'total_credit' => 4000]);
    JournalEntryLine::factory()->create(['journal_entry_id' => $expA->id, 'branch_id' => $this->branchA->id, 'account_id' => $this->rentAccount->id, 'debit' => 4000]);
    JournalEntryLine::factory()->create(['journal_entry_id' => $expA->id, 'branch_id' => $this->branchA->id, 'account_id' => $this->cashAccount->id, 'credit' => 4000]);

    // Branch B: Revenue 5000, Expense 1000 => Net Income 4000
    $revB = JournalEntry::factory()->create(['branch_id' => $this->branchB->id, 'date' => '2026-04-07', 'total_debit' => 5000, 'total_credit' => 5000]);
    JournalEntryLine::factory()->create(['journal_entry_id' => $revB->id, 'branch_id' => $this->branchB->id, 'account_id' => $this->cashAccount->id, 'debit' => 5000]);
    JournalEntryLine::factory()->create(['journal_entry_id' => $revB->id, 'branch_id' => $this->branchB->id, 'account_id' => $this->salesAccount->id, 'credit' => 5000]);

    $expB = JournalEntry::factory()->create(['branch_id' => $this->branchB->id, 'date' => '2026-04-08', 'total_debit' => 1000, 'total_credit' => 1000]);
    JournalEntryLine::factory()->create(['journal_entry_id' => $expB->id, 'branch_id' => $this->branchB->id, 'account_id' => $this->rentAccount->id, 'debit' => 1000]);
    JournalEntryLine::factory()->create(['journal_entry_id' => $expB->id, 'branch_id' => $this->branchB->id, 'account_id' => $this->cashAccount->id, 'credit' => 1000]);

    // Branch A report
    $respA = $this->getJson("/api/v1/accounting/reports/income-statement?branch_id={$this->branchA->id}&startDate=2026-04-01&endDate=2026-04-30");
    $respA->assertStatus(200);
    $dataA = $respA->json('data');
    expect($dataA['revenues']['total_revenue'])->toEqual(10000);
    expect($dataA['operating_activities']['total_expenses'])->toEqual(4000);
    expect($dataA['final_result']['net_income'])->toEqual(6000);

    // Branch B report
    $respB = $this->getJson("/api/v1/accounting/reports/income-statement?branch_id={$this->branchB->id}&startDate=2026-04-01&endDate=2026-04-30");
    $respB->assertStatus(200);
    $dataB = $respB->json('data');
    expect($dataB['revenues']['total_revenue'])->toEqual(5000);
    expect($dataB['operating_activities']['total_expenses'])->toEqual(1000);
    expect($dataB['final_result']['net_income'])->toEqual(4000);

    // Combined report
    $respAll = $this->getJson("/api/v1/accounting/reports/income-statement?startDate=2026-04-01&endDate=2026-04-30");
    $respAll->assertStatus(200);
    $dataAll = $respAll->json('data');
    expect($dataAll['revenues']['total_revenue'])->toEqual(15000);
    expect($dataAll['operating_activities']['total_expenses'])->toEqual(5000);
    expect($dataAll['final_result']['net_income'])->toEqual(10000);
});

test('general ledger report isolates entries by branch_id', function () {
    // Branch A transaction for Cash
    $entryA = JournalEntry::factory()->create(['branch_id' => $this->branchA->id, 'date' => '2026-04-10', 'total_debit' => 800, 'total_credit' => 800]);
    JournalEntryLine::factory()->create(['journal_entry_id' => $entryA->id, 'branch_id' => $this->branchA->id, 'account_id' => $this->cashAccount->id, 'debit' => 800, 'credit' => 0]);
    JournalEntryLine::factory()->create(['journal_entry_id' => $entryA->id, 'branch_id' => $this->branchA->id, 'account_id' => $this->capitalAccount->id, 'debit' => 0, 'credit' => 800]);

    // Branch B transaction for Cash
    $entryB = JournalEntry::factory()->create(['branch_id' => $this->branchB->id, 'date' => '2026-04-11', 'total_debit' => 1200, 'total_credit' => 1200]);
    JournalEntryLine::factory()->create(['journal_entry_id' => $entryB->id, 'branch_id' => $this->branchB->id, 'account_id' => $this->cashAccount->id, 'debit' => 1200, 'credit' => 0]);
    JournalEntryLine::factory()->create(['journal_entry_id' => $entryB->id, 'branch_id' => $this->branchB->id, 'account_id' => $this->capitalAccount->id, 'debit' => 0, 'credit' => 1200]);

    // Branch A GL for Cash
    $respA = $this->getJson("/api/v1/accounting/reports/general-ledger?accountId={$this->cashAccount->id}&branch_id={$this->branchA->id}&startDate=2026-04-01&endDate=2026-04-30");
    $respA->assertStatus(200);
    $dataA = $respA->json('data');
    expect($dataA['transactions'])->toHaveCount(1);
    expect((float) $dataA['transactions'][0]['debit'])->toEqual(800);
    expect($dataA['closing_balance'])->toEqual(800);

    // Branch B GL for Cash
    $respB = $this->getJson("/api/v1/accounting/reports/general-ledger?accountId={$this->cashAccount->id}&branch_id={$this->branchB->id}&startDate=2026-04-01&endDate=2026-04-30");
    $respB->assertStatus(200);
    $dataB = $respB->json('data');
    expect($dataB['transactions'])->toHaveCount(1);
    expect((float) $dataB['transactions'][0]['debit'])->toEqual(1200);
    expect($dataB['closing_balance'])->toEqual(1200);

    // Combined GL for Cash
    $respAll = $this->getJson("/api/v1/accounting/reports/general-ledger?accountId={$this->cashAccount->id}&startDate=2026-04-01&endDate=2026-04-30");
    $respAll->assertStatus(200);
    $dataAll = $respAll->json('data');
    expect($dataAll['transactions'])->toHaveCount(2);
    expect($dataAll['closing_balance'])->toEqual(2000);
});

test('balance sheet report isolates assets equity and retained earnings by branch_id', function () {
    // Branch A: Cash 6000 Dr, Capital 6000 Cr
    $entryA = JournalEntry::factory()->create(['branch_id' => $this->branchA->id, 'date' => '2026-04-01', 'total_debit' => 6000, 'total_credit' => 6000]);
    JournalEntryLine::factory()->create(['journal_entry_id' => $entryA->id, 'branch_id' => $this->branchA->id, 'account_id' => $this->cashAccount->id, 'debit' => 6000]);
    JournalEntryLine::factory()->create(['journal_entry_id' => $entryA->id, 'branch_id' => $this->branchA->id, 'account_id' => $this->capitalAccount->id, 'credit' => 6000]);

    // Branch B: Cash 4000 Dr, Capital 4000 Cr
    $entryB = JournalEntry::factory()->create(['branch_id' => $this->branchB->id, 'date' => '2026-04-01', 'total_debit' => 4000, 'total_credit' => 4000]);
    JournalEntryLine::factory()->create(['journal_entry_id' => $entryB->id, 'branch_id' => $this->branchB->id, 'account_id' => $this->cashAccount->id, 'debit' => 4000]);
    JournalEntryLine::factory()->create(['journal_entry_id' => $entryB->id, 'branch_id' => $this->branchB->id, 'account_id' => $this->capitalAccount->id, 'credit' => 4000]);

    // Branch A Balance Sheet
    $respA = $this->getJson("/api/v1/accounting/reports/balance-sheet?branch_id={$this->branchA->id}&endDate=2026-04-30");
    $respA->assertStatus(200);
    $dataA = $respA->json('data');
    expect($dataA['assets_group']['group_total'])->toEqual(6000);

    // Branch B Balance Sheet
    $respB = $this->getJson("/api/v1/accounting/reports/balance-sheet?branch_id={$this->branchB->id}&endDate=2026-04-30");
    $respB->assertStatus(200);
    $dataB = $respB->json('data');
    expect($dataB['assets_group']['group_total'])->toEqual(4000);
});
