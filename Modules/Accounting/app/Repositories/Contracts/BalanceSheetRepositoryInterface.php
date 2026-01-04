<?php

namespace Modules\Accounting\Repositories\Contracts;

interface BalanceSheetRepositoryInterface {

    public function getAccountsWithTotals($endDate);


}