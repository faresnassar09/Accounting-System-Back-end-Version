<?php

use App\Models\Tenant;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\Hash;
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
    $this->actingAs($this->user,'sanctum');

    $accountType = AccountType::where('type','opening_balance_diff')->first();
    $this->accountDiffBalancer = Account::factory()->create([

        'account_type_id' => $accountType->id, 
    ]);

});


it('ensures assets = liabilities + equity ',function(){


    $accounts = Account::factory()
    ->count(3)
    ->sequence(
        [
            'name' => 'Cash',
            'account_type_id' => AccountType::where('type','current_assets')->first()->id
        ],
        [
            'name' => 'Bank Loan',
            'account_type_id' => AccountType::where('type','current_liabilities')->first()->id
        ],
        [
            'name' => 'Retained Earnings',
            'account_type_id' => AccountType::where('type','retained_earnings')->first()->id
        ]
    )
    ->create();



    $JournalEntries = JournalEntry::factory()
    ->count(3)
    ->sequence(

        [
            'user_id' => auth()->id(),
            'reference' => '#asset',
            'description' => 'test asset entry',
            'date' => '2026-1-20',
            'total_debit' => 1000,
            'total_credit' => 1000
        ],
        [
            'user_id' => auth()->id(),
            'reference' => '#liabilitie',
            'description' => 'test liabilities entry',
            'date' => '2026-01-25',
            'total_debit' => 700,
            'total_credit' => 700
        ],  
        [
            'user_id' => auth()->id(),
            'reference' => '#equity',
            'description' => 'test equity entry',
            'date' => '2026-01-25',
            'total_debit' => 300,
            'total_credit' => 300
        ],
    )
    ->create();

    $entryLines = JournalEntryLine::factory()
    ->count(3)
    ->sequence(

        [
            'account_id' => $accounts->where('name', 'Cash')->first()->id,
            'journal_entry_id' => $JournalEntries->where('reference', '#asset')->first()->id,
            'debit' => 1000,
            'credit' => 0
        ],
        [
            'account_id' => $accounts->where('name', 'Bank Loan')->first()->id,
            'journal_entry_id' => $JournalEntries->where('reference', '#liabilitie')->first()->id,
            'debit' => 0,
            'credit' => 700
        ],        [
            'account_id' => $accounts->where('name', 'Retained Earnings')->first()->id,
            'journal_entry_id' => $JournalEntries->where('reference', '#equity')->first()->id,
            'debit' => 0,
            'credit' => 300
        ],
    )
    ->create();

    $data = ['endDate' => '2026-01-25'];
    
    $response = $this->postJson('api/v1/accounting/reports/balance-sheet',$data)
    ->assertStatus(200);

    $responseData = $response->json('data');
    $totalAssets = $responseData['assets_group']['group_total'];
    $totalLiabilities = $responseData['liabilities_and_equity_group']['sub_types']['liabilities_group']['type_total'];
    $totalEquity = $responseData['liabilities_and_equity_group']['sub_types']['equity_group']['type_total'];

    $this->assertEquals($totalAssets, $totalLiabilities + $totalEquity);
    $this->assertEquals(1000,$totalAssets);
    $this->assertEquals(700,$totalLiabilities);
    $this->assertEquals(300,$totalEquity);


});

afterEach(function () {
    if (tenancy()->initialized) {
        $tenant = tenancy()->tenant;
        tenancy()->end(); 
        $tenant->delete();
    }
});