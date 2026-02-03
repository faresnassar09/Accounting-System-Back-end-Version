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
});


test('trial balance debit and credit are balanced ', function () {


    $accounts = Account::factory()
        ->count(3)
        ->sequence(
            [

                'name' => 'Cash',
                'account_type_id' => AccountType::where('type', 'current_assets')->first()->id

            ],
            [
                'name' => 'Opreation Expenses',
                'account_type_id' => AccountType::where('type', 'non_operating_expenses')->first()->id
            ],
            [
                'name' => 'Salaries',
                'account_type_id' => AccountType::where('type', 'operating_expenses')->first()->id
            ],

        )->create();



    $JournalEntrieHeader = JournalEntry::factory()->create([

        'user_id' => auth()->id(),
        'reference' => '#asset',
        'description' => 'test asset entry',
        'date' => '2026-1-20',
        'total_debit' => 3800,
        'total_credit' => 3800

    ]);


    JournalEntryLine::factory()
        ->count(4)
        ->sequence(

            [
                'account_id' => $accounts->where('name','Cash')->first()->id,
                'journal_entry_id' => $JournalEntrieHeader->id,
                'debit' => 1500,
                'credit' => 0
            ],
            
            [
                'account_id' => $accounts->where('name','Cash')->first()->id,
                'journal_entry_id' => $JournalEntrieHeader->id,
                'debit' => 2300,
                'credit' => 0
            ],
            [
                'account_id' => $accounts->where('name','Opreation Expenses')->first()->id,
                'journal_entry_id' => $JournalEntrieHeader->id,
                'debit' => 0,
                'credit' => 1300
            ],
            [
                'account_id' => $accounts->where('name','Salaries')->first()->id,
                'journal_entry_id' => $JournalEntrieHeader->id,
                'debit' => 0,
                'credit' => 2500
            ],
        )
        ->create();

    $data = ['endDate' => '2026-01-25'];

    $response = $this->postJson('api/v1/accounting/reports/trial-balance', $data)
        ->assertStatus(200);

        $reportData =  $response->json('data');
        $totalDebit = $reportData['totals']['total_debit'];
        $totalCredit = $reportData['totals']['total_credit'];
        $balanced  = $reportData['totals']['isBalanced'];
 

        $this->assertEquals(3800, $totalDebit);
    $this->assertEquals(3800, $totalCredit);
    $this->assertTrue($balanced,'trial balance is balanced');
});

afterEach(function () {
    if (tenancy()->initialized) {
        $tenant = tenancy()->tenant;
        tenancy()->end();
        $tenant->delete();
    }
});
