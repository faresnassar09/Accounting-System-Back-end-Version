<?php

namespace Modules\Accounting\Queries;

use Illuminate\Support\Facades\DB;

class GeneralLedgerQuery
{
    public function __invoke(float $openingBalance, int $accountId, string $startDate, string $endDate): array
    {    
        $baseQuery = DB::table('journal_entry_lines as jl')
            ->join('journal_entries as je', 'jl.journal_entry_id', '=', 'je.id')
            ->where('jl.account_id', $accountId)
            ->whereBetween('je.date', [$startDate, $endDate]);

        $totals = (clone $baseQuery)
            ->selectRaw('
                COALESCE(SUM(jl.debit), 0.00) as total_debit,
                COALESCE(SUM(jl.credit), 0.00) as total_credit,
                COALESCE(SUM(jl.debit - jl.credit), 0.00) as net_movement
            ')
            ->first();

        $transactions = (clone $baseQuery)
            ->select(
                'je.date',
                'je.reference',
                'je.description',
                'jl.debit',
                'jl.credit'
            )
            ->selectRaw(
                '? + SUM(jl.debit - jl.credit) OVER (ORDER BY je.date, jl.id) as running_balance',
                [$openingBalance]
            )
            ->orderBy('je.date')
            ->orderBy('jl.id')
            ->get();

        return [
            'opening_balance' => $openingBalance,
            'closing_balance' => $openingBalance + (float) ($totals->net_movement ?? 0),
            'total_debit'     => (float) ($totals->total_debit ?? 0),
            'total_credit'    => (float) ($totals->total_credit ?? 0),
            'transactions'    => $transactions,
        ];
    }
}
