<?php

namespace Modules\Accounting\Repositories\Eloquent;

use Modules\Accounting\Models\AccountingMapping;
use Modules\Accounting\Enums\AccountingMappingType;
use Modules\Accounting\Repositories\Contracts\AccountingMappingRepositoryInterface;

class AccountingMappingRepository implements AccountingMappingRepositoryInterface
{

    public function getOpeningBalanceAccount()
    {

        $mapping = AccountingMapping::where('integration_key', AccountingMappingType::OPENING_DIFFERENT->value)->first();


        return $mapping?->account;
    }

    public function getCustomerAccount()
    {

        $mapping = AccountingMapping::where('integration_key', AccountingMappingType::PAYMENT_SYSTEM_CUSTOMER->value)->first();


        return $mapping?->account;
    }

    public function getProviderAccoount()
    {


        $mapping = AccountingMapping::where('integration_key', AccountingMappingType::PAYMENT_SYSTEM_PROVIDER->value)->first();


        return $mapping?->account;
    }
}
