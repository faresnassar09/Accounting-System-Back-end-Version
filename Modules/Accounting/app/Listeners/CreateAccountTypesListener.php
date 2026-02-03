<?php

namespace Modules\Accounting\Listeners;

use Modules\Accounting\Models\AccountType;
use Stancl\Tenancy\Events\DatabaseMigrated;

class CreateAccountTypesListener
{
    /**
     * Create the event listener.
     */
    public function __construct() {}

    /**
     * Handle the event.
     */
    public function handle(DatabaseMigrated $event): void
    {

        $accountTypes = [
            ['type' => 'gross_sales', 'account_group' => 'revenues'],
            ['type' => 'sales_deductions', 'account_group' => 'revenues'],
            ['type' => 'operating_revenue', 'account_group' => 'revenues'],
            ['type' => 'non_operating_revenue', 'account_group' => 'revenues'],

            ['type' => 'cogs', 'account_group' => 'expenses'],
            ['type' => 'operating_expenses', 'account_group' => 'expenses'],
            ['type' => 'non_operating_expenses', 'account_group' => 'expenses'],
            ['type' => 'income_tax_expenses', 'account_group' => 'expenses'],

            ['type' => 'current_assets', 'account_group' => 'assets'],
            ['type' => 'non_current_assets', 'account_group' => 'assets'],

            ['type' => 'current_liabilities', 'account_group' => 'liabilities'],
            ['type' => 'non_current_liabilities', 'account_group' => 'liabilities'],

            ['type' => 'equity_capital', 'account_group' => 'equity'],
            ['type' => 'retained_earnings', 'account_group' => 'equity'],
            ['type' => 'opening_balance_diff', 'account_group' => 'equity'],
        ];

        foreach ($accountTypes as $accountType) {

         AccountType::updateOrCreate([

                'type' => $accountType['type'],
                'account_group' => $accountType['account_group']

            ]);
        }
    }
}
