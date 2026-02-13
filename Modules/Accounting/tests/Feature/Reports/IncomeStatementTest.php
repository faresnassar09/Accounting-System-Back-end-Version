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

    $this->revenueType = AccountType::where('type', 'operating_revenue')->first();
    $this->expenseType = AccountType::where('type', 'operating_expenses')->first();
});

test('income statement calculates net profit correctly from revenue and expenses', function () {
    $salesAccount = Account::factory()->create([
        'name' => 'Sales Revenue',
        'account_type_id' => $this->revenueType->id
    ]);
    
    $rentAccount = Account::factory()->create([
        'name' => 'Office Rent',
        'account_type_id' => $this->expenseType->id
    ]);

    $cashAccount = Account::factory()->create();

    $revenueEntry = JournalEntry::factory()->create(['date' => '2026-01-10', 'total_debit' => 10000, 'total_credit' => 10000]);
    JournalEntryLine::factory()->create(['journal_entry_id' => $revenueEntry->id, 'account_id' => $cashAccount->id, 'debit' => 10000]);
    JournalEntryLine::factory()->create(['journal_entry_id' => $revenueEntry->id, 'account_id' => $salesAccount->id, 'credit' => 10000]);

    $expenseEntry = JournalEntry::factory()->create(['date' => '2026-01-15', 'total_debit' => 4000, 'total_credit' => 4000]);
    JournalEntryLine::factory()->create(['journal_entry_id' => $expenseEntry->id, 'account_id' => $rentAccount->id, 'debit' => 4000]);
    JournalEntryLine::factory()->create(['journal_entry_id' => $expenseEntry->id, 'account_id' => $cashAccount->id, 'credit' => 4000]);

    $response = $this->getJson('api/v1/accounting/reports/income-statement?startDate=2026-01-01&endDate=2026-01-31');
    $data = $response->json('data');

    \Log::info('kop',[$data]);

    $totalRevenue = $data['revenues']['total_revenue']; 
    $totalExpenses = $data['operating_activities']['total_expenses'];
    $netIncome = $data['final_result']['net_income'];

    expect($totalRevenue)->toBe(10000);
    expect($totalExpenses)->toBe(4000);
    expect($netIncome)->toBe(6000); 
    expect($data['final_result']['is_profit'])->toBeTrue();
});

test('income statement handles net loss scenario', function () {
    $salesAccount = Account::factory()->create(['account_type_id' => $this->revenueType->id]);
    $expenseAccount = Account::factory()->create(['account_type_id' => $this->expenseType->id]);
    $cashAccount = Account::factory()->create();

    $entry = JournalEntry::factory()->create(['date' => '2026-01-20', 'total_debit' => 7000, 'total_credit' => 7000]);
    
    JournalEntryLine::factory()->create(['journal_entry_id' => $entry->id, 'account_id' => $salesAccount->id, 'credit' => 2000]);
    JournalEntryLine::factory()->create(['journal_entry_id' => $entry->id, 'account_id' => $expenseAccount->id, 'debit' => 5000]);
    JournalEntryLine::factory()->create(['journal_entry_id' => $entry->id, 'account_id' => $cashAccount->id, 'debit' => 2000, 'credit' => 5000]);

    $response = $this->getJson('api/v1/accounting/reports/income-statement?startDate=2026-01-01&endDate=2026-01-31');

    expect($response->json('data.final_result.net_income'))->toBe(-3000);
});

afterEach(function () {
    if (tenancy()->initialized) {
        $tenant = tenancy()->tenant;
        tenancy()->end();
        $tenant->delete();
    }
});