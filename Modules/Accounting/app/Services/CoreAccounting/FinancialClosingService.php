<?php

namespace Modules\Accounting\Services\CoreAccounting;

use App\Services\Api\ApiResponseFormatter;
use App\Services\Logging\LoggerService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Modules\Accounting\Repositories\Contracts\AccountRepositoryInterface;
use Modules\Accounting\Repositories\Contracts\FinancialClosingInterface;
use Modules\Accounting\Repositories\Contracts\FinancialClosingReposiroryInterface;
use Modules\Accounting\Repositories\Contracts\IncomeStatementRepositoryInterface;
use Modules\Accounting\Repositories\Contracts\JournalEntryRepositoryInterface;
use Modules\Accounting\Services\Reports\IncomeStatementService;
use Modules\Accounting\Transformers\PreviewTotalClosing;

class FinancialClosingService
{

    public function __construct(

        public FinancialClosingReposiroryInterface $financialClosingInterFace,
        public IncomeStatementService $incomeStatmentService,
        public AccountRepositoryInterface $accountRepositoryInterface,
        public IncomeStatementRepositoryInterface $incomeRepositoryInterface,
        public JournalEntryRepositoryInterface $journalRepositoryInterface,
        public LoggerService $loggerService,

    ) {}


    public  function getRevenuesAndExpenses($year)
    {

        $startDate = "$year-1-1";
        $endDate = "$year-12-30";

        $data = $this->incomeStatmentService->generateReport($startDate, $endDate);

        $totalRevenues = $data['total_revenue'];

        $totalExpenses = $data['total_expenses'] +
            $data['total_cogs'] +
            (isset($data['non_operating_net']) ? abs($data['non_operating_net']) : 0) +
            $data['tax_expense_total'];

        $data = [

            'total_revenues' => $totalRevenues,
            'total_expenses' => $totalExpenses,
            'net_profit' => $data['net_income']
        ];

        return $data;
    }

    public function applyClosingFinancialYear($data)
    {

        $accountId = $data->account_id;
        $year = $data->year;
        $startFrom = "$year-1-1";
        $endAt = "$year-12-30";


        $alreadyClosed = $this->financialClosingInterFace->isYearClosed($year);

        if ($alreadyClosed) {

            throw new \Exception("Financial Year ( $year ) Already Closed");
        }

        $accounts = $this->incomeRepositoryInterface->getRevenueExpenseAccounts(
            $startFrom,
            $endAt);
            
            $raintrdEarningsAccount = $this->accountRepositoryInterface->findAccount($accountId);

            $lines =collect( $this->prepareClosingLines($accounts));

            $totalDebit = $lines->sum('debit');
            $totalCredit = $lines->sum('credit');
            $totalAmount = max($totalDebit, $totalCredit);
            $diffTotals = $totalDebit - $totalCredit;    

             $this->reverseAndBalanceAccountsTotals(
                $lines,
                $totalAmount,
                $diffTotals,
                $year,
                $raintrdEarningsAccount->id);

    }

    private function reverseAndBalanceAccountsTotals(
        $lines,
        $totalAmount,
        $diffTotals,
        $year,
        $raintrdEarningsAccountId){

        try {

        DB::transaction(function() use ($lines,$totalAmount,$diffTotals,$year,$raintrdEarningsAccountId){

            $header = [

                'date' => now(),
                'reference' => Str::uuid(),
                "description" => "closing journal for year ($year)",
                
              ];

               $journalHeader =  $this->journalRepositoryInterface->store(
                $header,
                $lines,
                $totalAmount,
                'closing');
                
                if ($diffTotals != 0) {

                    $this->journalRepositoryInterface->storeDiffBalancerLines(
                    $journalHeader,
                    $diffTotals,
                    $raintrdEarningsAccountId,
                );

                }

                $this->financialClosingInterFace->flagYearAsClosed(
                    $year,
                    abs($diffTotals),
                    $raintrdEarningsAccountId);


        });

        } catch (\Exception $e) {

            $this->loggerService->failedLogger('l;k',[],$e);

        }



    }

    private function prepareClosingLines($data){

        return $data->filter(function ($account){

            return ($account['total_debit'] > 0  || $account['total_credit'] > 0 );

        })->map(function($query){

            return[

                'account_id' => $query->id,
                'debit' =>$query->total_debit,
                'credit' => $query->total_credit,

            ];
        });


    }
}
