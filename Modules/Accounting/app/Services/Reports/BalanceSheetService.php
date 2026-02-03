<?php

namespace Modules\Accounting\Services\Reports;

use App\Services\Api\ApiResponseFormatter;
use App\Services\Logging\LoggerService;
use Carbon\Carbon;

use Modules\Accounting\Queries\GetAccountCumulativeBalancesQuery;
use Modules\Accounting\Queries\GetOpeningBalanceQuery;
use Modules\Accounting\Queries\GetProfitAndLossDetailsQuery;
use Modules\Accounting\Transformers\BalanceSheetResource;

class BalanceSheetService
{

    public function __construct(

        public GetProfitAndLossDetailsQuery $ProfitLossAccounts,
        public GetOpeningBalanceQuery $getOpeningBalance,
        public LoggerService $loggerService,
        public GetAccountCumulativeBalancesQuery $accountBalancesQuery,
        public ApiResponseFormatter $apiResponseFormatter,
        public IncomeStatementService $incomeStatementService,
    ) {}

    public function generateReport($endDate)
    {

        $startOfYear = get_start_of_year($endDate);
        $profitLossAccounts = ($this->ProfitLossAccounts)($startOfYear, $endDate)->pluck('id');

        $accounts =  ($this->accountBalancesQuery)($endDate);

        $classifiedData = $this->processBalanceSheetData($accounts);

        $netProfitValue = ($this->getOpeningBalance)($profitLossAccounts, $endDate);

        return $this->formatDataArray($classifiedData, $netProfitValue);
        
    }

    private function processBalanceSheetData($accounts)
    {

        $accounts = $this->calculateNetBalanceAndDefineAccountType($accounts);

        $classifications = [
            'current_assets',
            'non_current_assets',
            'current_liabilities',
            'non_current_liabilities',
            'equity_capital',
            'retained_earnings',
            'opening_balance_diff',

        ];

        $results = [];
        foreach ($classifications as $group) {
            $groupAccounts = $accounts->where('type', $group);

            $results[$group] = (float) $groupAccounts->sum('netBalance');
            $results[$group . '_details'] = $groupAccounts->values();
        }

        return $results;
    }

    private function calculateNetBalanceAndDefineAccountType($accounts)
    {
        return $accounts->map(function ($account) {
            $totalDebit = (float) $account->debit;
            $totalCredit = (float) $account->credit;

            $type = $account->type;
            $netBalance = abs($totalDebit - $totalCredit);

            return [
                'id'         => $account->id,
                'name'       => $account->name,
                'type'       => $type,
                'netBalance' => $netBalance,
            ];
        });
    }

    private function formatDataArray($classifiedData, $netProfitValue)
    {


        return [

            'assets_group'=>[
                'group_code' => 'assets',
                'group_name' => 'assets',
                'group_total' => $classifiedData['current_assets'] +
                    $classifiedData['non_current_assets'],

                'sub_types' => [
                    [
                        'type_code' => 'current_assets',
                        'type_name' => 'Current Assets',
                        'type_total' => $classifiedData['current_assets'],
                        'accounts' => $classifiedData['current_assets_details']
                    ],

                    [
                        'type_code' => 'non_current_assets',
                        'type_name' => 'Non Current Assets',
                        'type_total' => $classifiedData['non_current_assets'],
                        'accounts' => $classifiedData['non_current_assets_details']
                    ]
                ]
            ],
            'liabilities_and_equity_group' => [
                'group_code' => 'liabilities_and_equity',
                'group_name' => 'Liabilities And Equity',
                'group_total' => $classifiedData['current_liabilities'] +
                    $classifiedData['non_current_liabilities'] +
                    $classifiedData['equity_capital'] +
                    $classifiedData['retained_earnings'] +
                    $classifiedData['opening_balance_diff'] +
                     abs($netProfitValue),
                'sub_types' => [
                    'liabilities_group' => [
                        'type_code' => 'current_liabilities',
                        'type_name' => 'Current Liabilities',
                        'type_total' => $classifiedData['current_liabilities'] +
                            $classifiedData['non_current_liabilities'],

                        'accounts' => $classifiedData['current_liabilities_details']
                    ],

                    'equity_group' => [

                        'type_code' => 'equity',
                        'type_name' => 'Owners Rights',
                        'type_total' =>
                        $classifiedData['equity_capital'] +
                            $classifiedData['retained_earnings']+
                            $classifiedData['opening_balance_diff'],



                        'accounts' => $classifiedData['equity_capital_details']
                            ->merge($classifiedData['retained_earnings_details'])
                            ->push([
                                'id' => null,
                                'name' => 'Net Profit / Loss (Current)',
                                'netBalance' => $netProfitValue
                            ])
                    ]
                ]
            ]
        ];
    }
}
