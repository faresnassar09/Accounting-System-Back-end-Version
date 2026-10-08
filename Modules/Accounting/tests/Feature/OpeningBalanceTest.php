<?php

use App\Models\Tenant;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Passport\Passport;
use Modules\Accounting\Models\Account;
use Modules\Accounting\Enums\AccountingMappingType;
use Modules\Accounting\Models\AccountingMapping;
use Modules\Accounting\Models\AccountType;
use Modules\Authorization\Models\Role;
use Modules\User\Models\User;
use Tests\TestCase;





uses(TestCase::class, DatabaseMigrations::class);

beforeEach(function () {

    $this->tenant = Tenant::create();
    $this->tenant->domains()->create(['domain' => 'tenant1.localhost']);
    tenancy()->initialize($this->tenant);



    $this->user = User::factory()->create();
    $role = Role::create(['name' => 'accountant' , 'guard_name' => 'web']);
    $this->user->assignRole($role);

    Passport::actingAs($this->user);

    $accountType = AccountType::where('type','opening_balance_diff')->first();


});


test('user can add opening balance journal entry',function(){


    $account = Account::factory()->create();

AccountingMapping::where('integration_key', AccountingMappingType::OPENING_DIFFERENT->value)
    ->update(['account_id' => $account->id]);

    $data = [

        'header' => [

            'reference' => '#12',
            'date' => now(),
            'description' => 'test opening journal entry',
            
        ],
        
        'lines' => [

            [
                'account_id' => $account->id,
                'debit' => 100,
                'credit' => 0
            ],
        ]
        ];

        $this->postJson('api/v1/accounting/opening-balances',$data)
        ->assertStatus(201);
        
        $this->assertDatabaseHas('journal_entries',[

            'reference' => $data['header']['reference']
        ]);

        $this->assertDatabaseHas('journal_entry_lines',[

            'account_id' => $account->id,
            'credit' => 100
        ]);


});

afterEach(function () {
    if (tenancy()->initialized) {
        $tenant = tenancy()->tenant;
        tenancy()->end(); 
        $tenant->delete();
    }
});