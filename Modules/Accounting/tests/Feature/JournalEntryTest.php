<?php

use App\Models\Tenant;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\Hash;
use Modules\Accounting\Models\Account;
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

    $this->account = Account::factory()->create();

});   

test('unauthorized user cannot create journal entry', function () {
    $anotherUser = User::factory()->create();
    $this->actingAs($anotherUser);

    $response = $this->postJson('api/v1/accounting/store-journal-entries', [
        'header' => ['description' => 'Unauthorized attemp'],
        'lines' => []
    ]);

    $response->assertStatus(403); 
});

test('user can create a entry journal ',function(){



    $data = [

        'header' => [
            'reference' => rand(1,5),
            'date' => now(),
            'description' => 'test entry',
            'total_debit' => 1000,
            'total_credit' => 1000,
        ],

        'lines' => [
            
            [

            'account_id' => $this->account->id,
            'debit' => 1000,
            'credit'=> 0,
        ],
        [

            
            'account_id' => $this->account->id,
            'debit' => 0,
            'credit'=> 1000,
        ]]
    ];

    $response = $this->postJson('api/v1/accounting/store-journal-entries',$data,[

        'Accept' => 'application/json'
    ]);

    $response->assertStatus(200);
    $this->assertDatabaseHas('journal_entries',
     ['reference' => $data['header']['reference']]);

     $this->assertDatabaseHas('journal_entry_lines', [
        'account_id' => $this->account->id
    ]);

});


test("can't create unbalanced journal entry",function(){

    $data = [

            'header' => [
                'reference' => 'UNBALANCED-REF',
                'date' => now(),
                'description' => 'test entry',
                'total_debit' => 1000,
                'total_credit' => 900,
            ],

            'lines' => [

                [

                    'account_id' => $this->account->id,
                    'debit' => 1000,
                    'credit'=> 0,
                ],
                [
        
                    
                    'account_id' => $this->account->id,
                    'debit' => 0,
                    'credit'=> 900,
                ]
                ] 

    ];



    $response = $this->postJson('api/v1/accounting/store-journal-entries',$data,
[
    'Accept' => 'application/json'
]);

    $response->assertStatus(422);
    $this->assertDatabaseMissing('journal_entries',
     ['reference' => $data['header']['reference']]);

     $this->assertDatabaseMissing('journal_entry_lines', [
        'account_id' => $this->account->id
    ]);

});

test("can't create journal entry with a duplicate reference", function () {

    $commonReference = 'REF-100';

    $data1 = [
        'header' => [
            'reference' => $commonReference,
            'date' => now(),
            'description' => 'First Entry',
            'total_debit' => 1000,
            'total_credit' => 1000,
        ],
        'lines' => [
            ['account_id' => $this->account->id, 'debit' => 1000, 'credit' => 0],
            ['account_id' => $this->account->id, 'debit' => 0, 'credit' => 1000]
        ]
    ];
    $this->postJson('api/v1/accounting/store-journal-entries', $data1);

    $data2 = $data1; 
    $data2['header']['description'] = 'Duplicate Entry Attempt';

    $response = $this->postJson('api/v1/accounting/store-journal-entries', $data2);

    $response->assertStatus(422);
    
    $response->assertJsonValidationErrors(['header.reference']);
});

afterEach(function () {
    if (tenancy()->initialized) {
        $tenant = tenancy()->tenant;
        tenancy()->end(); 
        $tenant->delete();
    }
});