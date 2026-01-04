<?php

namespace Modules\Accounting\Services\Reports;

use App\Services\Api\ApiResponseFormatter;
use App\Services\Logging\LoggerService;
use Modules\Accounting\Repositories\Contracts\BalanceSheetRepositoryInterface;
use Modules\Accounting\Transformers\BalanceSheetResource;

class BalanceSheetService
{

    public function __construct(

        public BalanceSheetRepositoryInterface $balanceSheetRepositoryInterface,
        public LoggerService $loggerService,
        public ApiResponseFormatter $apiResponseFormatter,
        public IncomeStatementService $incomeStatementService,
    ) {}

    public function generateReport($endDate)
    {

        try {

            $accounts =  $this->balanceSheetRepositoryInterface->getAccountsWithTotals($endDate);

            $classifiedData = $this->processBalanceSheetData($accounts);

            $incomeStatmentReport = $this->incomeStatementService
                ->generateReport('1990-1-1', $endDate);

            $incomeStatmentReport = $incomeStatmentReport->getData(true);

            $netProfitValue = $incomeStatmentReport['data']['revenues']['net_sales'];
         
            $data = $this->formatDataArray($classifiedData,$netProfitValue);

            return $this->apiResponseFormatter->successResponse(

                'Balance Sheet Report Generated Successfully',
                BalanceSheetResource::collection($data),
                
            );
            
        } catch (\Exception $e) {


            $this->loggerService->failedLogger(

                'Error Occurred While Generating Balance Sheet',
                [],
                $e->getMessage()
            );
        }
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
            $totalDebit = (float) $account->total_debit;
            $totalCredit = (float) $account->total_credit;

            $type = $account->accountType?->type;

            $creditTypes = [
                'current_liabilities',
                'non_current_liabilities',
                'equity_capital',
            ];

            if (in_array($type, $creditTypes)) {
                $netBalance = $totalCredit - $totalDebit;
            } else {

                $netBalance = $totalDebit - $totalCredit;
            }

            return [
                'id'         => $account->id,
                'name'       => $account->name,
                'type'       => $type,
                'netBalance' => $netBalance,
            ];
        });
    }

    private function formatDataArray($classifiedData,$netProfitValue){


       return [

            [
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
            [
                'group_code' => 'liabilities_and_equity',
                'group_name' => 'Liabilities And Equity',
                'group_total' => $classifiedData['current_liabilities'] +
                    $classifiedData['non_current_liabilities'] +
                    $classifiedData['equity_capital'] +
                    $classifiedData['retained_earnings'] +
                    $netProfitValue,
                'sub_types' => [
                    [
                        'type_code' => 'current_liabilities',
                        'type_name' => 'Current Liabilities',
                        'type_total' => $classifiedData['current_liabilities'],
                        'accounts' => $classifiedData['current_liabilities_details']
                    ],

                    [

                        'type_code' => 'equity',
                        'type_name' => 'Owners Rights',
                        'type_total' => $classifiedData['current_liabilities'] +
                            $classifiedData['non_current_liabilities'] +
                            $classifiedData['equity_capital'] +
                            $classifiedData['retained_earnings'] +
                            $netProfitValue,

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
