<?php

use \Filament\Actions\DeleteAction;
use App\Models\Tenant;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Livewire\Livewire;
use Modules\Accounting\Models\Account;
use Modules\Admin\Filament\Resources\Accounts\Pages\CreateAccount;
use Modules\Admin\Filament\Resources\Accounts\Pages\ListAccounts;
use Modules\Admin\Models\Admin;
use Modules\User\Models\User;
use Spatie\Permission\Models\Role;
use Tests\TestCase;





uses(TestCase::class, DatabaseMigrations::class);

beforeEach(function () {
    $this->tenant1 = Tenant::create();

    $this->tenant1->domains()->create(['domain' => 'tenant1.app.test']);

    tenancy()->initialize($this->tenant1);


    $this->admin = Admin::factory()->create();

    // There Is listener creates a super_admin role automatically
    // check  Modules/Admin/Listeners/CreateSuperAdminListener.php


    $this->admin->assignRole('super_admin');
    $this->actingAs($this->admin, 'admin');

    $this->user = User::factory()->create();
    $role = Role::create(['name' => 'accountant' , 'guard_name' => 'web']);
    $this->user->assignRole($role);
});

/** -------------------------------------------------------
 * 1. (Creation)
 * ------------------------------------------------------- */

it('validates required fields and unique account number', function () {


    Livewire::test(CreateAccount::class)
        ->fillForm(['number' => '', 'name' => ''])
        ->call('create')
        ->assertHasFormErrors(['number' => 'required', 'name' => 'required']);

    Account::create(['number' => '101', 'name' => 'Cash', 'type' => 'asset']);

    Livewire::test(CreateAccount::class)
        ->fillForm(['number' => '101', 'name' => 'Another Cash'])
        ->call('create')
        ->assertHasFormErrors(['number' => 'unique']);
});

/** -------------------------------------------------------
 *          (Tenancy Isolation)
 * ------------------------------------------------------- */

it('prevents tenant A from seeing accounts of tenant B', function () {
    // 1. Create a specific account for the first tenant (initialized in beforeEach)
    $account1 = Account::create([
        'number' => '111',
        'name' => 'T1 Account',
        'type' => 'asset'
    ]);

    // 2. End the session for the first tenant
    tenancy()->end();

    // 3. Create a second tenant and a Admin for it
    $tenant2 = Tenant::create();
    $tenant2->domains()->create(['domain' => 'tenant2.app.test']);
    tenancy()->initialize($tenant2);

    $admin2 = Admin::factory()->create();
    $this->actingAs($admin2);

    // Expect 5 accounts (not 0) because a listener adds 5 main accounts automatically
    // Check: Modules/Accounting/app/Listeners/CreateMainAccountsListener.php

    Livewire::test(ListAccounts::class)
        ->assertCanNotSeeTableRecords([$account1])
        ->assertCountTableRecords(5);
});


// /** -------------------------------------------------------
//  * 3. Test Authorization 
//  * ------------------------------------------------------- */

it('forbids unauthorized Admins from accessing account creation', function () {

    $unauthorizedAdmin = Admin::factory()->create();
    $this->actingAs($unauthorizedAdmin);

    Livewire::test(CreateAccount::class)
        ->assertForbidden();
});

/** -------------------------------------------------------
 * 4. (Deletion & Business Rules)
 * ------------------------------------------------------- */

 it('prevents deletion of account if it has transactions', function () {
    $account = Account::create(['number' => '201', 'name' => 'Bank', 'type' => 'asset']);

    $this->actingAs($this->user,'web');


    $data = [
        'header' => [
            'reference' => 1, 
            'date' => now()->format('Y-m-d'),
            'description' => 'Initial balance',
            'total_debit' => 1000,
            'total_credit' => 1000,
        ],
        'lines' => [
            ['account_id' => $account->id, 'debit' => 1000, 'credit' => 0],
            ['account_id' => $account->id, 'debit' => 0, 'credit' => 1000],
        ]
    ];

    $this->postJson('api/v1/accounting/journal-entries', $data);

    Livewire::test(\Modules\Admin\Filament\Resources\Accounts\Pages\EditAccount::class, [
        'record' => $account->getRouteKey(),
    ])
    ->callAction(DeleteAction::class) 
    ->assertHasNoErrors();

});

test('user can see Accounting Chart',function(){

    $this->actingAs($this->user,'sanctum');

    $response = $this->get('api/v1/accounting/charts');

    $response->assertStatus(200);

});


afterEach(function () {
    if (tenancy()->initialized) {
        $tenant = tenancy()->tenant;
        tenancy()->end(); 
        $tenant->delete();
    }
});