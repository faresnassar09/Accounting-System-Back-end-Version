<?php

namespace Modules\Accounting\Queries;

use Illuminate\Support\Facades\DB;
use Modules\Accounting\Models\Account;

class TrialBalanceQuery
{
    public function __invoke(string $startOfYear, string $endDate, ?int $branchId = null): array
    {
        $subQuery = DB::table('accounts')
            ->leftJoin('journal_entry_lines as ji', 'accounts.id', '=', 'ji.account_id')
            ->join('journal_entries as je', function ($join) use ($startOfYear, $endDate) {
                $join->on('ji.journal_entry_id', '=', 'je.id')
                     ->whereBetween('je.date', [$startOfYear, $endDate]);
            })
            ->when($branchId, function ($q, $branchId) {
                return $q->where('ji.branch_id', $branchId);
            })
            ->select('accounts.id', 'accounts.name', 'accounts.number')
            ->selectRaw("
                COALESCE(SUM(CASE WHEN je.type = 'opening' THEN ji.debit ELSE 0 END), 0.00) as opening_debit,
                COALESCE(SUM(CASE WHEN je.type = 'opening' THEN ji.credit ELSE 0 END), 0.00) as opening_credit,
                COALESCE(SUM(CASE WHEN je.type != 'opening' THEN ji.debit - ji.credit ELSE 0 END), 0.00) as net_period
            ")
            ->groupBy('accounts.id', 'accounts.name', 'accounts.number');

        $accounts = DB::query()->fromSub($subQuery, 'sub')
            ->select(
                'sub.id',
                'sub.name',
                'sub.number',
                'sub.opening_debit',
                'sub.opening_credit'
            )
            ->selectRaw("
                CASE WHEN sub.net_period > 0 THEN sub.net_period ELSE 0.00 END as period_debit,
                CASE WHEN sub.net_period < 0 THEN ABS(sub.net_period) ELSE 0.00 END as period_credit,
                (CASE WHEN sub.net_period > 0 THEN sub.net_period ELSE 0.00 END) + sub.opening_debit as final_debit_balance,
                (CASE WHEN sub.net_period < 0 THEN ABS(sub.net_period) ELSE 0.00 END) + sub.opening_credit as final_credit_balance
            ")
            ->get();

        $totals = DB::query()->fromSub($subQuery, 'sub')
            ->selectRaw("
                COALESCE(SUM((CASE WHEN sub.net_period > 0 THEN sub.net_period ELSE 0.00 END) + sub.opening_debit), 0.00) as total_debit,
                COALESCE(SUM((CASE WHEN sub.net_period < 0 THEN ABS(sub.net_period) ELSE 0.00 END) + sub.opening_credit), 0.00) as total_credit
            ")
            ->first();

        $totalDebit = (float) ($totals->total_debit ?? 0.00);
        $totalCredit = (float) ($totals->total_credit ?? 0.00);

        return [
            'accounts' => $accounts,
            'totals'   => [
                'total_debit'  => $totalDebit,
                'total_credit' => $totalCredit,
                'isBalanced'   => $totalDebit === $totalCredit,
            ],
        ];
    }
}
