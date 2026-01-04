<?php 

namespace Modules\Accounting\Repositories\Contracts;

interface GeneralLdegerRepositoryInterface{

    public function getOpeningBalance($accountId,$startDate);
    public function getAccountPeriodTotals($accountId,$startDate,$endDate);
    public function getTransactions($accountId,$startDate,$endDate);
}