<?php

namespace Modules\Accounting\Queries;

use Illuminate\Support\Facades\DB;

class IncomeStatementQuery
{
    public function __invoke(string $startDate, string $endDate, ?int $branchId = null): array
    {
        // 1. Shared base query for profit and loss transactions in date range
        $baseQuery = DB::table('journal_entry_lines as jl')
            ->join('journal_entries as je', 'jl.journal_entry_id', '=', 'je.id')
            ->join('accounts as a', 'jl.account_id', '=', 'a.id')
            ->join('account_types as at', 'a.account_type_id', '=', 'at.id')
            ->whereBetween('je.date', [$startDate, $endDate])
            ->whereIn('at.account_group', ['revenues', 'expenses'])
            ->when($branchId, fn($q) => $q->where('jl.branch_id', $branchId));

        // 2. Database-computed group sums
        $groupsSubQuery = (clone $baseQuery)
            ->selectRaw("
                COALESCE(SUM(CASE WHEN at.type = 'gross_sales' THEN (jl.credit - jl.debit) ELSE 0.00 END), 0.00) as gross_sales,
                COALESCE(SUM(CASE WHEN at.type = 'sales_deductions' THEN (jl.debit - jl.credit) ELSE 0.00 END), 0.00) as sales_deductions,
                COALESCE(SUM(CASE WHEN at.type = 'operating_revenue' THEN (jl.credit - jl.debit) ELSE 0.00 END), 0.00) as operating_revenue,
                COALESCE(SUM(CASE WHEN at.type = 'non_operating_revenue' THEN (jl.credit - jl.debit) ELSE 0.00 END), 0.00) as non_operating_revenue,
                COALESCE(SUM(CASE WHEN at.type = 'cogs' THEN (jl.debit - jl.credit) ELSE 0.00 END), 0.00) as cogs,
                COALESCE(SUM(CASE WHEN at.type = 'operating_expenses' THEN (jl.debit - jl.credit) ELSE 0.00 END), 0.00) as operating_expenses,
                COALESCE(SUM(CASE WHEN at.type = 'non_operating_expenses' THEN (jl.debit - jl.credit) ELSE 0.00 END), 0.00) as non_operating_expenses,
                COALESCE(SUM(CASE WHEN at.type = 'income_tax_expenses' THEN (jl.debit - jl.credit) ELSE 0.00 END), 0.00) as income_tax_expenses
            ");

        // 3. Database-computed P&L KPIs directly in SQL — ZERO PHP math
        $metrics = DB::query()->fromSub($groupsSubQuery, 'g')
            ->select(
                'g.gross_sales',
                'g.sales_deductions',
                'g.operating_revenue',
                'g.non_operating_revenue',
                'g.cogs',
                'g.operating_expenses',
                'g.non_operating_expenses',
                'g.income_tax_expenses'
            )
            ->selectRaw("
                (g.gross_sales - ABS(g.sales_deductions)) as net_sales,
                (g.gross_sales - ABS(g.sales_deductions) + g.operating_revenue) as total_revenue,
                (g.gross_sales - ABS(g.sales_deductions) + g.operating_revenue - g.cogs) as gross_profit,
                (g.gross_sales - ABS(g.sales_deductions) + g.operating_revenue - g.cogs - g.operating_expenses) as operating_income,
                (g.non_operating_revenue - g.non_operating_expenses) as non_operating_net,
                ((g.gross_sales - ABS(g.sales_deductions) + g.operating_revenue - g.cogs - g.operating_expenses) + (g.non_operating_revenue - g.non_operating_expenses)) as income_before_tax,
                ((g.gross_sales - ABS(g.sales_deductions) + g.operating_revenue - g.cogs - g.operating_expenses) + (g.non_operating_revenue - g.non_operating_expenses) - g.income_tax_expenses) as net_income
            ")
            ->first();

        // 4. Database-computed account line items
        $accounts = (clone $baseQuery)
            ->select(
                'a.id',
                'a.name',
                'a.number',
                'at.account_group',
                'at.type'
            )
            ->selectRaw("
                SUM(
                    CASE 
                        WHEN at.type = 'sales_deductions' THEN (jl.debit - jl.credit)
                        WHEN at.account_group = 'revenues' THEN (jl.credit - jl.debit) 
                        WHEN at.account_group = 'expenses' THEN (jl.debit - jl.credit) 
                        ELSE (jl.debit - jl.credit) 
                    END
                ) as balance
            ")
            ->groupBy('a.id', 'a.name', 'a.number', 'at.account_group', 'at.type')
            ->orderBy('at.account_group', 'desc')
            ->get();

        return [
            'metrics' => [
                'net_sales'              => (float) $metrics->net_sales,
                'operating_revenue'      => (float) $metrics->operating_revenue,
                'total_revenue'          => (float) $metrics->total_revenue,
                'gross_sales'            => (float) $metrics->gross_sales,
                'sales_deductions'       => (float) $metrics->sales_deductions,
                'total_cogs'             => (float) $metrics->cogs,
                'gross_profit'           => (float) $metrics->gross_profit,
                'total_expenses'         => (float) $metrics->operating_expenses,
                'operating_income'       => (float) $metrics->operating_income,
                'non_operating_revenue'  => (float) $metrics->non_operating_revenue,
                'non_operating_expenses' => (float) $metrics->non_operating_expenses,
                'non_operating_net'      => (float) $metrics->non_operating_net,
                'income_before_tax'      => (float) $metrics->income_before_tax,
                'tax_expense_total'      => (float) $metrics->income_tax_expenses,
                'net_income'             => (float) $metrics->net_income,
            ],
            'accounts' => $accounts,
        ];
    }
}
