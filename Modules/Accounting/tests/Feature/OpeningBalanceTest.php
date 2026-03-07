<?php

use App\Models\Tenant;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Passport\ClientRepository;
use Laravel\Passport\Passport;
use Modules\Accounting\Models\Account;
use Modules\Accounting\Models\AccountType;
use Tests\TestCase;





uses(TestCase::class, DatabaseMigrations::class);

beforeEach(function () {

    $this->tenant = Tenant::create();
    $this->tenant->domains()->create(['domain' => 'tenant1.localhost']);
    tenancy()->initialize($this->tenant);



            $this->clients = app(ClientRepository::class);

$this->client = $this->clients->createClientCredentialsGrantClient(
    'main',             
);

    Passport::actingAsClient($this->client, ['*']);

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

        $this->postJson('api/v1/accounting/opening-balances',$data)
        ->assertStatus(201);
        
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