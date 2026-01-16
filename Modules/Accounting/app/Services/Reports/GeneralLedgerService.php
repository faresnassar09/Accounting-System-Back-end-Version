<?php

namespace Modules\Accounting\Services\Reports;

use App\Services\Api\ApiResponseFormatter;
use App\Services\Logging\LoggerService;
use Modules\Accounting\Repositories\Contracts\AccountRepositoryInterface as AccountInterface;
use Modules\Accounting\Repositories\Contracts\GeneralLdegerRepositoryInterface as GeneralLedgerInterface;
use Modules\Accounting\Transformers\GeneralLedgerResource;

class GeneralLedgerService
{

    public $accountEntries = null;

    public function __construct(
        public AccountInterface $AccountInterface,
        public GeneralLedgerInterface $generalLedgerInterface,
        public ApiResponseFormatter $apiResponseFormatter,
        public LoggerService $loggerService,

    ) {}



    public function getAccountEntries($data)
    {

        try {

            $account = $this->AccountInterface->getAccount($data->accountId);
            $accountId = $account->id;
            $startDate = $data->startDate;
            $endDate = $data->endDate;

            $opningBalance = $this->generalLedgerInterface->getOpeningBalance(

                $accountId,
                $startDate,
            );

            $periodTotals = $this->generalLedgerInterface->getAccountPeriodTotals(
                $accountId,
                $startDate,
                $endDate
            );

            $closingBalance = $opningBalance
                + $periodTotals['total_debit']
                - $periodTotals['total_credit'];

            $transactions = $this->generalLedgerInterface->getTransactions(
                $accountId,
                $startDate,
                $endDate
            );

            return $this->apiResponseFormatter->successResponse(


                'Ledger Report Generated Successfully',

                new GeneralLedgerResource(

                    [
                        'account_info' => ['name' => $account->name, 'number' => $account->number],

                        'opening_balance' => $opningBalance,
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


        return  $this->accountEntries;
    }
}
