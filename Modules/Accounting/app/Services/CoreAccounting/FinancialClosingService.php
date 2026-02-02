<?php

namespace Modules\Accounting\Services\CoreAccounting;

use App\Services\Logging\LoggerService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Modules\Accounting\Queries\GetAccountCumulativeBalancesQuery;
use Modules\Accounting\Queries\GetProfitAndLossDetailsQuery;
use Modules\Accounting\Queries\GetProfitAndLossTotalsQuery;
use Modules\Accounting\Repositories\Contracts\FinancialClosingReposiroryInterface;
use Modules\Accounting\Repositories\Contracts\JournalEntryRepositoryInterface;

class FinancialClosingService
{

    public function __construct(

        public FinancialClosingReposiroryInterface $financialClosingInterFace,
        public GetProfitAndLossTotalsQuery $getProfitAndLossTotalsQuery,
        public GetProfitAndLossDetailsQuery $getProfitAndLossDetailsQuery,
        public GetAccountCumulativeBalancesQuery $getAccountsBalance,
        public JournalEntryRepositoryInterface $journalRepositoryInterface,
        public LoggerService $loggerService,

    ) {}


    public  function getRevenuesAndExpenses($year)
    {

        $startDate = get_start_of_year($year);
        $endDate = get_end_of_year($year);
        $summary = ($this->getProfitAndLossTotalsQuery)($startDate, $endDate);

        $totalRevenues = $summary->total_revenues ?? 0;
        $totalExpenses = $summary->total_expenses ?? 0;

        $finalExpenses = $totalExpenses;
        $netProfit     = $totalRevenues - $finalExpenses;

        $summary = [

            'total_revenues' => $totalRevenues,
            'total_expenses' => $totalExpenses,
            'net_profit' =>  $netProfit
        ];

        return $summary;
    }

    public function applyClosingFinancialYear($data)
    {

        $accountId = $data->account_id;
        $year = $data->year;
        $startFrom = get_start_of_year($year);
        $endAt = get_end_of_year($year);


            DB::transaction(function () use (
                $year,
                $endAt,
                $startFrom,
                $accountId,

            ) {

                $alreadyClosed = $this->financialClosingInterFace->isYearClosed($year);

                if ($alreadyClosed) {

                    throw new \Exception("Financial Year ( $year ) Already Closed");
                }


                $accounts = ($this->getProfitAndLossDetailsQuery)(
                    $startFrom,
                    $endAt
                );

                $lines = collect($this->prepareClosingLines($accounts));


                $totalDebit = $lines->sum('debit');
                $totalCredit = $lines->sum('credit');
                $totalAmount = max($totalDebit, $totalCredit);
                $diffTotals = abs($totalCredit - $totalDebit);


                $header = [

                    'date' => now(),
                    'reference' => Str::uuid(),
                    "description" => "closing journal for year ($year)",
                    'total_debit' => $totalAmount,
                    'total_credit' => $totalAmount,
                ];

                $journalHeader =  $this->journalRepositoryInterface->store(
                    $header,
                    $lines,
                    'closing'
                );

                if ($diffTotals != 0) {

                    $this->journalRepositoryInterface->storeDiffBalancerLines(
                        $journalHeader,
                        $diffTotals,
                        $accountId,
                    );
                }

                $this->financialClosingInterFace->flagYearAsClosed(
                    $year,
                    abs($diffTotals),
                    $accountId
                );

                $balances = ($this->getAccountsBalance)($endAt);
                $lines = collect($this->prepareClosingLinesForcurrentAccounts($balances));
                $year = get_start_of_next_financial_year($year);
                \Log::info('ojpo',[$year]);
                $totalDebit = $lines->sum('debit');
                $totalCredit = $lines->sum('credit');
                $totalAmount = max($totalDebit, $totalCredit);

                $header = [
                    'date' => "$year-1-1",
                    'reference' => Str::uuid(),
                    "description" => "Opening journal for year ($year)",
                    'total_debit' => $totalAmount,
                    'total_credit' => $totalAmount,
                ];


                $journalHeader =  $this->journalRepositoryInterface->store(
                    $header,
                    $lines,
                    'opening'
                );
            });

            return true;
    }

    private function prepareClosingLines($data)
    {

        return $data->map(function ($query) {

            $balance = $query->balance;
            $baseType = $query->account_group;

            return [
                'account_id' => $query->id,
                'debit' => $baseType === "revenues" && $query->type !==  'sales_deductions'? $balance : 0.00,
                'credit' => $baseType === "expenses" || $query->type === 'sales_deductions' ?$balance : 0.00,

            ];
        });
    }
    
    private function prepareClosingLinesForcurrentAccounts($data)
    {

        return $data->map(function ($query) {

            $totalDebit = $query->debit;
            $totalCredit = $query->credit;
            return [
                'account_id' => $query->id,
                'debit' => $totalDebit,
                'credit' => $totalCredit,

            ];
        });
    }
}
