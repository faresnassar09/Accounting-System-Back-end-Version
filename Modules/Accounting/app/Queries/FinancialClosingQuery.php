<?php

namespace Modules\Accounting\Queries;

use Illuminate\Support\Facades\DB;

class FinancialClosingQuery
{
    /**
     * Generate P&L closing lines and exact totals in SQL (Zero PHP math)
     */
    public function getClosingPnlData(string $startDate, string $endDate, ?int $userId = null): array
    {
        $basePnlQuery = DB::table('journal_entry_lines as jl')
            ->join('journal_entries as je', 'jl.journal_entry_id', '=', 'je.id')
            ->join('accounts as a', 'jl.account_id', '=', 'a.id')
            ->join('account_types as at', 'a.account_type_id', '=', 'at.id')
            ->whereBetween('je.date', [$startDate, $endDate])
            ->whereIn('at.account_group', ['revenues', 'expenses'])
            ->where('je.type', '!=', 'closing');

        $pnlAccountBalances = DB::query()->fromSub(
            (clone $basePnlQuery)
                ->select('a.id', 'at.account_group', 'at.type')
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
                ->groupBy('a.id', 'at.account_group', 'at.type'),
            'pnl'
        );

        $lines = (clone $pnlAccountBalances)
            ->select('pnl.id as account_id')
            ->selectRaw("
                CASE WHEN pnl.account_group = 'revenues' AND pnl.type != 'sales_deductions' THEN pnl.balance ELSE 0.00 END as debit,
                CASE WHEN pnl.account_group = 'expenses' OR pnl.type = 'sales_deductions' THEN pnl.balance ELSE 0.00 END as credit
            ")
            ->get()
            ->map(function ($row) use ($userId) {
                return [
                    'account_id'       => $row->account_id,
                    'debit'            => (float) $row->debit,
                    'credit'           => (float) $row->credit,
                    'source_reference' => $userId,
                ];
            });

        $totals = (clone $pnlAccountBalances)
            ->selectRaw("
                COALESCE(SUM(CASE WHEN pnl.account_group = 'revenues' AND pnl.type != 'sales_deductions' THEN pnl.balance ELSE 0 END), 0.00) as total_debit,
                COALESCE(SUM(CASE WHEN pnl.account_group = 'expenses' OR pnl.type = 'sales_deductions' THEN pnl.balance ELSE 0 END), 0.00) as total_credit,
                CASE 
                    WHEN COALESCE(SUM(CASE WHEN pnl.account_group = 'revenues' AND pnl.type != 'sales_deductions' THEN pnl.balance ELSE 0 END), 0.00) >
                         COALESCE(SUM(CASE WHEN pnl.account_group = 'expenses' OR pnl.type = 'sales_deductions' THEN pnl.balance ELSE 0 END), 0.00)
                    THEN COALESCE(SUM(CASE WHEN pnl.account_group = 'revenues' AND pnl.type != 'sales_deductions' THEN pnl.balance ELSE 0 END), 0.00)
                    ELSE COALESCE(SUM(CASE WHEN pnl.account_group = 'expenses' OR pnl.type = 'sales_deductions' THEN pnl.balance ELSE 0 END), 0.00)
                END as total_amount,
                ABS(
                    COALESCE(SUM(CASE WHEN pnl.account_group = 'expenses' OR pnl.type = 'sales_deductions' THEN pnl.balance ELSE 0 END), 0.00) -
                    COALESCE(SUM(CASE WHEN pnl.account_group = 'revenues' AND pnl.type != 'sales_deductions' THEN pnl.balance ELSE 0 END), 0.00)
                ) as diff_totals
            ")
            ->first();

        return [
            'lines'        => $lines,
            'total_debit'  => (float) ($totals->total_debit ?? 0.00),
            'total_credit' => (float) ($totals->total_credit ?? 0.00),
            'total_amount' => (float) ($totals->total_amount ?? 0.00),
            'diff_totals'  => (float) ($totals->diff_totals ?? 0.00),
        ];
    }

    /**
     * Generate opening lines for next financial year and exact totals in SQL (Zero PHP math)
     */
    public function getNextYearOpeningData(string $endDate, ?int $userId = null): array
    {
        $baseBalanceQuery = DB::table('accounts as a')
            ->join('account_types as at', 'a.account_type_id', '=', 'at.id')
            ->join('journal_entry_lines as ji', 'a.id', '=', 'ji.account_id')
            ->join('journal_entries as je', 'ji.journal_entry_id', '=', 'je.id')
            ->whereDate('je.date', '<=', $endDate)
            ->whereIn('at.account_group', ['assets', 'liabilities', 'equity']);

        $balanceSheetAccounts = DB::query()->fromSub(
            (clone $baseBalanceQuery)
                ->select('a.id')
                ->selectRaw("
                    CASE WHEN SUM(ji.debit - ji.credit) > 0 THEN SUM(ji.debit - ji.credit) ELSE 0.00 END as debit,
                    CASE WHEN SUM(ji.debit - ji.credit) < 0 THEN ABS(SUM(ji.debit - ji.credit)) ELSE 0.00 END as credit
                ")
                ->groupBy('a.id'),
            'bs'
        );

        $lines = (clone $balanceSheetAccounts)
            ->select('bs.id as account_id', 'bs.debit', 'bs.credit')
            ->get()
            ->map(function ($row) use ($userId) {
                return [
                    'account_id'       => $row->account_id,
                    'debit'            => (float) $row->debit,
                    'credit'           => (float) $row->credit,
                    'source_reference' => $userId,
                ];
            });

        $totals = (clone $balanceSheetAccounts)
            ->selectRaw("
                COALESCE(SUM(bs.debit), 0.00) as total_debit,
                COALESCE(SUM(bs.credit), 0.00) as total_credit,
                CASE 
                    WHEN COALESCE(SUM(bs.debit), 0.00) > COALESCE(SUM(bs.credit), 0.00)
                    THEN COALESCE(SUM(bs.debit), 0.00)
                    ELSE COALESCE(SUM(bs.credit), 0.00)
                END as total_amount
            ")
            ->first();

        return [
            'lines'        => $lines,
            'total_debit'  => (float) ($totals->total_debit ?? 0.00),
            'total_credit' => (float) ($totals->total_credit ?? 0.00),
            'total_amount' => (float) ($totals->total_amount ?? 0.00),
        ];
    }
}
