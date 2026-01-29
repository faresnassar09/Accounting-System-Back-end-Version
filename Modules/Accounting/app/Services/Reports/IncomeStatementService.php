<?php

namespace Modules\Accounting\Services\Reports;

use Modules\Accounting\Queries\GetProfitAndLossDetailsQuery;

class IncomeStatementService
{

    public function __construct(

        public GetProfitAndLossDetailsQuery $profitAndLossAccounts,


    ) {}

    public function generateReport($startDate, $endDate)
    {

        $accounts =  ($this->profitAndLossAccounts)($startDate, $endDate);
        $processedData =  $this->processIncomeStatementData($accounts);

        return $this->preparedReportData($startDate, $endDate, $processedData);
    }

    private function processIncomeStatementData($accounts)
    {

        $groupedAccounts = $accounts->groupBy('type');
        $classifications = [
            'gross_sales',
            'sales_deductions',
            'operating_revenue',
            'non_operating_revenue',
            'cogs',
            'operating_expenses',
            'non_operating_expenses',
            'income_tax_expenses'
        ];
        $results = [];
        foreach ($classifications as $group) {

            $groupData = $groupedAccounts->get($group, collect([]));
            $sum = $groupData->sum('balance');

            $results[$group] = $sum;
            $results[$group . '_details'] = $groupData->values();
        }
        return $results;
    }

    private function preparedReportData($startDate, $endDate, $processedData)
    {

        $netSales = $processedData['gross_sales'] - $processedData['sales_deductions'];

        $totalOperatingRevenue = $netSales + $processedData['operating_revenue'];
        $grossProfit = $totalOperatingRevenue - $processedData['cogs'];

        $operatingIncome = $grossProfit - $processedData['operating_expenses'];


        $nonOperatingNet = $processedData['non_operating_revenue'] - $processedData['non_operating_expenses'];

        $incomeBeforeTax = $operatingIncome + $nonOperatingNet;

        $netIncome = $incomeBeforeTax - $processedData['income_tax_expenses'];
        
        return  [
            'start_date' => $startDate,
            'end_date'   => $endDate,

            'net_sales'         => $netSales,
            'operating_revenue' => $processedData['operating_revenue'],
            'total_revenue'     => $totalOperatingRevenue,

            'gross_sales'      => $processedData['gross_sales'],
            'sales_deductions' => $processedData['sales_deductions'],

            'gross_sales_details'       => $processedData['gross_sales_details'],
            'sales_deductions_details'  => $processedData['sales_deductions_details'],
            'operating_revenue_details' => $processedData['operating_revenue_details'],

            'total_cogs'   => $processedData['cogs'],
            'gross_profit' => $grossProfit,
            'cogs_details' => $processedData['cogs_details'],

            'total_expenses'   => $processedData['operating_expenses'],
            'operating_income' => $operatingIncome,
            'operating_expenses_details' => $processedData['operating_expenses_details'],

            'non_operating_net' => $nonOperatingNet,
            'non_operating_revenue_details'  => $processedData['non_operating_revenue_details'],
            'non_operating_expenses_details' => $processedData['non_operating_expenses_details'],

            'income_before_tax' => $incomeBeforeTax,
            'tax_expense_total' => $processedData['income_tax_expenses'],
            'tax_expenses_details' => $processedData['income_tax_expenses_details'] ?? [],

            'net_income' => $netIncome,
        ];
    }
}
