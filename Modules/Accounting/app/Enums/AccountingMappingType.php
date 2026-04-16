<?php

namespace Modules\Accounting\Enums;

enum  AccountingMappingType: string{

    case OPENING_DIFFERENT = 'opening_diff_balance';
    case PAYMENT_SYSTEM_CUSTOMER = 'payment_system_customer';
    case PAYMENT_SYSTEM_PROVIDER = 'payment_system_provider';
    case PLATFORM_FEES = 'platform_fees';
    

public function label(): string
    {
        return match($this) {
            self::OPENING_DIFFERENT => 'Opening Different Balance',
            self::PAYMENT_SYSTEM_CUSTOMER => 'Customer Clearing Account',
            self::PAYMENT_SYSTEM_PROVIDER => 'Merchant Settlement Account',
            self::PLATFORM_FEES => 'Platform Service Revenue',
        };
    }

    public function description (): string
    
    {
            return match($this) {
            self::OPENING_DIFFERENT => 'All Diff Balance Will Go To The Associated Account.',
            self::PAYMENT_SYSTEM_CUSTOMER => 'Handles all individual customer wallet transactions.',
            self::PAYMENT_SYSTEM_PROVIDER => 'Handles balances due to merchants and external providers.',
            self::PLATFORM_FEES => 'Platform Service Revenue',
        };
    }
}
   
