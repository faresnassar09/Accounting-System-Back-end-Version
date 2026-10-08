<?php 

namespace Modules\Accounting\Repositories\Contracts;

interface AccountingMappingRepositoryInterface{

    public function getOpeningBalanceAccount();
    public function getCustomerAccount();
    public function getProviderAccoount();

}