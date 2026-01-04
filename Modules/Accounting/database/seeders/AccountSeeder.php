<?php

namespace Modules\Accounting\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AccountSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        DB::table('accounts')->insert([
            [ 'name' => 'Assets', 'number' => 1 ,'initial_balance' => 0, ],
            [ 'name' => 'Liabilities', 'number' => 2 , 'initial_balance' => 0],
            [ 'name' => 'Equity', 'number' => 3, 'initial_balance' => 0],
            [ 'name' => 'Revenue', 'number' => 4, 'initial_balance' => 0 ],
            [ 'name' => 'Expenses', 'number' => 5, 'initial_balance' => 0 ],
        ]);      

    }
}
