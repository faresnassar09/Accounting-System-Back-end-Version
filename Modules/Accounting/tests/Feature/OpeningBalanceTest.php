<?php

use App\Models\Tenant;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\Hash;
use Modules\Accounting\Models\Account;
use Modules\Accounting\Models\AccountType;
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


test('user can add opening balance journal entry',function(){


    $account = Account::factory()->create();
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

        $this->postJson('api/v1/accounting/store-opening-balance',$data)
        ->assertStatus(200);
        
        $this->assertDatabaseHas('journal_entries',[

            'reference' => $data['header']['reference']
        ]);

        $this->assertDatabaseHas('journal_entry_lines',[

            'account_id' => $this->accountDiffBalancer->id,
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