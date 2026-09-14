<?php

namespace Modules\Accounting\Services\Reports;

use Modules\Accounting\Queries\TrialBalanceQuery;

class TrialBalanceService
{
    
    public function __construct(
        public TrialBalanceQuery $trialBalanceQuery,
    ) {}

    public function generateReport(string $endDate): array
    {
        $startOfYear = get_start_of_year($endDate);

        $reportData = ($this->trialBalanceQuery)($startOfYear, $endDate);

        return [
            'endDate'    => $endDate,
            'reportData' => $reportData['accounts'],
            'totals'     => $reportData['totals'],
        ];
    }
}
