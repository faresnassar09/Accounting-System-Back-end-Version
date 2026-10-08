<?php

namespace Modules\Accounting\Listeners;

use Modules\Accounting\Enums\AccountingMappingType;
use Modules\Accounting\Models\AccountingMapping;
use Stancl\Tenancy\Events\DatabaseMigrated;

class CreateAccountingMappingsListener
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

        $accountMappings = [
            [
                'label' => AccountingMappingType::OPENING_DIFFERENT->label(),
                'integration_key' => AccountingMappingType::OPENING_DIFFERENT->value,
                'account_id' => null,
                'description' => AccountingMappingType::OPENING_DIFFERENT->description(),
            ],

            [
                'label'    => AccountingMappingType::PAYMENT_SYSTEM_CUSTOMER->label(),
                'integration_key' => AccountingMappingType::PAYMENT_SYSTEM_CUSTOMER->value,
                'account_id' => null,
                'description'     => AccountingMappingType::PAYMENT_SYSTEM_CUSTOMER->description(),
            ],
            [
                'label'    => AccountingMappingType::PAYMENT_SYSTEM_PROVIDER->label(),
                'integration_key' => AccountingMappingType::PAYMENT_SYSTEM_PROVIDER->value,
                'account_id' => null,
                'description' => AccountingMappingType::PAYMENT_SYSTEM_PROVIDER->description(),
            ],
            [
                'label'    => AccountingMappingType::PLATFORM_FEES->label(), 
                'integration_key' => AccountingMappingType::PLATFORM_FEES->value,
                'account_id' => null,
                'description'     => AccountingMappingType::PLATFORM_FEES->description(),
            ],
        ];

        foreach ($accountMappings as $accountMapping) {

            AccountingMapping::updateOrCreate([

                'label' => $accountMapping['label'],
                'integration_key' => $accountMapping['integration_key'],
                'account_id' => $accountMapping['account_id'],
                'description' => $accountMapping['description'],

            ]);
        }
    }
}
