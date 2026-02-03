<?php

use App\Models\Tenant;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Modules\Accounting\Models\Account;
use Modules\Accounting\Models\AccountType;
use Modules\Accounting\Models\JournalEntry;
use Modules\Accounting\Models\JournalEntryLine;
use Modules\User\Models\User;
use Spatie\Permission\Models\Role;
use Tests\TestCase;



uses(TestCase::class,DatabaseMigrations::class);

beforeEach(function () {

    $this->tenant = Tenant::create();
    $this->tenant->domains()->create(['domain' => 'tenant1.app.test']);
    tenancy()->initialize($this->tenant);

    $this->user = User::factory()->create();
    $role = Role::create(['name' => 'accountant']);

    $this->user->assignRole($role);
    $this->actingAs($this->user, 'sanctum');


    $this->accounts = Account::factory()
        ->count(2)
        ->sequence(

            [

                'name' => 'Cash',
                'account_type_id' => AccountType::where('type', 'current_assets')->first()->id

            ],
            [

                'name' => 'Bank Loan',
                'account_type_id' => AccountType::where('type', 'non_current_liabilities')->first()->id

            ],


        )->create();



    $this->JournalEntrieHeader = JournalEntry::factory()
        ->count(2)
        ->sequence(
            [

                'user_id' => auth()->id(),
                'reference' => '#cash1',
                'description' => 'test cash1 entry',
                'date' => '2026-01-01',
                'total_debit' => 1200,
                'total_credit' => 1200

            ],

            [
                'user_id' => auth()->id(),
                'reference' => '#cash2',
                'description' => 'test cash2 entry',
                'date' => '2026-02-01',
                'total_debit' => 5000,
                'total_credit' => 5000
            ]


        )->create();


    JournalEntryLine::factory()
        ->count(4)
        ->sequence(

            [
                'account_id' => $this->accounts->where('name', 'Cash')->first()->id,
                'journal_entry_id' => $this->JournalEntrieHeader->where('reference', '#cash1')->first()->id,
                'debit' => 1200,
                'credit' => 0
            ],

            [
                'account_id' => $this->accounts->where('name', 'Bank Loan')->first()->id,
                'journal_entry_id' => $this->JournalEntrieHeader->where('reference', '#cash1')->first()->id,
                'credit' => 0,
                'credit' => 1200,
            ],
            [
                'account_id' => $this->accounts->where('name', 'Cash')->first()->id,
                'journal_entry_id' => $this->JournalEntrieHeader->where('reference', '#cash2')->first()->id,
                'debit' => 2300,
                'debit' => 5000,
                'credit' => 0
            ],
            [
                'account_id' => $this->accounts->where('name', 'Bank Loan')->first()->id,
                'journal_entry_id' => $this->JournalEntrieHeader->where('reference', '#cash2')->first()->id,
                'debit' => 2300,
                'debit' => 0,
                'credit' => 5000
            ],
        )
        ->create();
});


test('trial balance calculates totals correctly', function () {

    $data = [

        'accountId' => $this->accounts->where('name', 'Cash')->first()->id,

        'startDate' => '2026-01-01',
        'endDate' => '2026-02-03'
    ];

    $response = $this->postJson('api/v1/accounting/reports/general-ledger', $data)
        ->assertStatus(200);

        
        $closingBalance = $response->json('data.closing_balance');

        expect($closingBalance)->toBe(6200);
});


test('trial balance ignores entries after the specified end date', function () {

    $data = [

        'accountId' => $this->accounts->where('name', 'Cash')->first()->id,

        'startDate' => '2026-01-01',
        'endDate' => '2026-01-30'
    ];

    $response = $this->postJson('api/v1/accounting/reports/general-ledger', $data)
        ->assertStatus(200);

        $closingBalance = $response->json('data.closing_balance');

        expect($closingBalance)->toBe(1200);
 });

 test('trial balance returns empty results when no entries exist',function(){


    $data = [

        'accountId' => $this->accounts->where('name', 'Cash')->first()->id,

        'startDate' => '2025-01-01',
        'endDate' => '2025-01-30'
    ];

    $response = $this->postJson('api/v1/accounting/reports/general-ledger', $data)
        ->assertStatus(200);

        $closingBalance = $response->json('data.closing_balance');

        expect($closingBalance)->toBe(0);

 });

 test('old balance calculated as opening balance', function () {

    $data = [

        'accountId' => $this->accounts->where('name', 'Cash')->first()->id,

        'startDate' => '2026-01-20',
        'endDate' => '2026-02-03'
    ];

    $response = $this->postJson('api/v1/accounting/reports/general-ledger', $data)
        ->assertStatus(200);

        $closingBalance = $response->json('data.closing_balance');
        $openingBalance = $response->json('data.opening_balance');
        $totalDebit = $response->json('data.total_debit');
        
        expect($openingBalance)->toBe(1200);
        expect($totalDebit)->toBe(5000);
        expect($closingBalance)->toBe(6200);
});


afterEach(function () {
    if (tenancy()->initialized) {
        $tenant = tenancy()->tenant;
        tenancy()->end();
        $tenant->delete();
    }
});
