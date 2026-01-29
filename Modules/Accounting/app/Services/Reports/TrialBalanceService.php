<?php

namespace Modules\Accounting\Services\Reports;

use App\Services\Api\ApiResponseFormatter;
use App\Services\Logging\LoggerService;
use Carbon\Carbon;
use Modules\Accounting\Queries\TrialBalanceQuery;

class TrialBalanceService
{
    
    public function __construct(
        public ApiResponseFormatter $apiResponseFormatter,
        public TrialBalanceQuery $trialBalanceQuery,
        public LoggerService $loggerService,
    ) {}

    public function generateReport($endDate)
    {

        $startOfYear = get_start_of_year($endDate);

        $totals = [
            'opening_debit' => 0,
            'opening_credit' => 0,
        ];
        $reportData = ($this->trialBalanceQuery)($startOfYear, $endDate)
            ->map(function ($query) use (&$totals) {

                $total_debit =  $query->total_debit;
                $total_credit = $query->total_credit;
                $netBalance = $total_debit - $total_credit;

                $query->period_debit = $netBalance > 0 ? abs($netBalance) : 0.00;
                $query->period_credit = $netBalance < 0 ? abs($netBalance) : 0.00;

                $query->final_debit_balance = $query->period_debit +
                    $query->opening_debit;

                $query->final_credit_balance = $query->period_credit +
                    $query->opening_credit;

                $totals['opening_debit'] += $query->opening_debit;
                $totals['opening_credit'] += $query->opening_credit;

                return $query;
            });

        $totalOpeningDebit = $totals['opening_debit'];
        $totalOpeningCredit = $totals['opening_credit'];

        $totalGrandDebit = $reportData->sum('period_debit') + $totalOpeningDebit;

        $totalGrandCredit = $reportData->sum('period_credit') + $totalOpeningCredit;

        $isBalanced = $totalGrandDebit === $totalGrandCredit;

        return
            [

                'endDate' => $endDate,
                'reportData' => $reportData,
                'totals' => [

                    'total_debit' => $totalGrandDebit,
                    'total_credit' => $totalGrandCredit,

                    'isBalanced' => $isBalanced,

                ],

            ];
    }
}
