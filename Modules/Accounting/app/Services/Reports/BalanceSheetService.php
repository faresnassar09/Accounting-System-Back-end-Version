<?php

namespace Modules\Accounting\Services\Reports;

use Modules\Accounting\Queries\BalanceSheetQuery;
use Modules\Accounting\Queries\GetOpeningBalanceQuery;
use Modules\Accounting\Queries\GetProfitAndLossDetailsQuery;

class BalanceSheetService
{
    public function __construct(
        public BalanceSheetQuery $balanceSheetQuery,
        public GetProfitAndLossDetailsQuery $profitLossAccounts,
        public GetOpeningBalanceQuery $getOpeningBalance,
    ) {}

    public function generateReport(?string $endDate = null): array
    {
        $endDate = $endDate ?: now()->format('Y-m-d');
        $startOfYear = get_start_of_year($endDate);

        $profitLossAccountIds = ($this->profitLossAccounts)($startOfYear, $endDate)->pluck('id');
        $netProfitValue = ($this->getOpeningBalance)($profitLossAccountIds, $endDate);

        return ($this->balanceSheetQuery)($endDate, $netProfitValue);
    }
}

