<?php

namespace Modules\Accounting\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Modules\Accounting\Models\Account;
use Stancl\Tenancy\Events\DatabaseMigrated;

class CreateMainAccountsListener
{
    /**
     * Create the event listener.
     */
    public function __construct() {}

    public function handle(DatabaseMigrated $event): void {


        $accounts = [ 

            [ 'name' => 'assets' , 'number' => 1, 'description' => 'The Main Aseets Account'],
            [ 'name' => 'liabilities' , 'number' => 2 , 'description' => 'The Main Liabilities Account'],
            [ 'name' => 'equity' , 'number' => '3','description' => 'The Main Equity Account'],
            [ 'name' => 'revenues', 'number' => 4 , 'description' => 'The Main Revenues Account'],
            [ 'name' => 'expenses' , 'number' => 5 , 'description' => 'The Main Expenses Account'],
        ];

        foreach ($accounts as $account){

            Account::updateOrCreate([

                'name' => $account['name'],
                'parent_id' => null,
                'account_type_id' => null,
                'number' => $account['number'],
                'description' => $account['description'],
            ]);
        }

    }
}
