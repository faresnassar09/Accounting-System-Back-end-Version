<?php

namespace Modules\Accounting\Services\Reports;

use App\Services\Api\ApiResponseFormatter;
use App\Services\Logging\LoggerService;
use Modules\Accounting\Queries\GeneralLedgerQuery;
use Modules\Accounting\Queries\GetOpeningBalanceQuery;
use Modules\Accounting\Repositories\Contracts\AccountRepositoryInterface as AccountInterface;
use Modules\Accounting\Transformers\GeneralLedgerResource;

class GeneralLedgerService
{
    public function __construct(
        public AccountInterface $AccountInterface,
        public GetOpeningBalanceQuery $getOpeningBalance,
        public GeneralLedgerQuery $generalLedgerQuery,
        public ApiResponseFormatter $apiResponseFormatter,
        public LoggerService $loggerService,

    ) {}

    public function generateReport($data)
    {

        try {

            $account = $this->AccountInterface->findAccount($data->accountId);
            $accountId = $account->id;
            $startDate = $data->startDate;
            $endDate = $data->endDate;

            $openingBalance = ($this->getOpeningBalance)([$accountId], $startDate);
            $ReportData = ($this->generalLedgerQuery)($openingBalance, $accountId, $startDate, $endDate);
            $transactions = collect($ReportData['transactions']);

            $openingBalance = $ReportData['opening_balance'];

            $periodTotals = [
                'total_debit'  => $transactions->sum('debit'),
                'total_credit' => $transactions->sum('credit'),
                'final_balance' => $openingBalance +
                    $transactions->sum('debit') -
                        $transactions->sum('credit')
            ];

            $closingBalance = $periodTotals['final_balance'];


            return $this->apiResponseFormatter->successResponse(


                'General Ledger Report Generated Successfully',

                new GeneralLedgerResource(

                    [
                        'account_info' => [
                            'name' => $account->name,
                            'number' => $account->number
                        ],

                        'opening_balance' => $openingBalance,
                        'closing_balance' => $closingBalance,
                        'total_debit' => $periodTotals['total_debit'],
                        'total_credit' => $periodTotals['total_credit'],
                        'transactions' => $transactions

                    ]

                )



            );
        } catch (\Exception $e) {

            $this->loggerService->failedLogger(

                'Error Occurred While Generating General Ledger Report',
                [],
                $e->getMessage()
            );

            return $this->apiResponseFormatter->failedResponse(

                'Failed To Generate General Ledger Report',
                [],
                500,
            );
        }
    }
}
