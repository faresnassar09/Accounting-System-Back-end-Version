<?php

namespace Modules\Accounting\Services\Reports;

use Modules\Accounting\Queries\IncomeStatementQuery;

class IncomeStatementService
{
    public function __construct(
        public IncomeStatementQuery $incomeStatementQuery,
    ) {}

    public function generateReport(?string $startDate = null, ?string $endDate = null): array
    {
        $endDate   = $endDate ?: now()->format('Y-m-d');
        $startDate = $startDate ?: get_start_of_year($endDate);

        $report = ($this->incomeStatementQuery)($startDate, $endDate);

        $groupedAccounts = $report['accounts']->groupBy('type');
        $metrics = $report['metrics'];

        return [
            'start_date' => $startDate,
            'end_date'   => $endDate,

            'net_sales'         => $metrics['net_sales'],
            'operating_revenue' => $metrics['operating_revenue'],
            'total_revenue'     => $metrics['total_revenue'],

            'gross_sales'      => $metrics['gross_sales'],
            'sales_deductions' => $metrics['sales_deductions'],

            'gross_sales_details'       => $groupedAccounts->get('gross_sales', collect())->values(),
            'sales_deductions_details'  => $groupedAccounts->get('sales_deductions', collect())->values(),
            'operating_revenue_details' => $groupedAccounts->get('operating_revenue', collect())->values(),

            'total_cogs'   => $metrics['total_cogs'],
            'gross_profit' => $metrics['gross_profit'],
            'cogs_details' => $groupedAccounts->get('cogs', collect())->values(),

            'total_expenses'             => $metrics['total_expenses'],
            'operating_income'           => $metrics['operating_income'],
            'operating_expenses_details' => $groupedAccounts->get('operating_expenses', collect())->values(),

            'non_operating_net'              => $metrics['non_operating_net'],
            'non_operating_revenue_details'  => $groupedAccounts->get('non_operating_revenue', collect())->values(),
            'non_operating_expenses_details' => $groupedAccounts->get('non_operating_expenses', collect())->values(),

            'income_before_tax'    => $metrics['income_before_tax'],
            'tax_expense_total'    => $metrics['tax_expense_total'],
            'tax_expenses_details' => $groupedAccounts->get('income_tax_expenses', collect())->values(),

            'net_income' => $metrics['net_income'],
        ];
    }
}

