<?php

namespace Modules\Accounting\Queries;

use Illuminate\Support\Facades\DB;

class GeneralLedgerQuery
{
    public function __invoke(float $openingBalance, int $accountId, string $startDate, string $endDate)
    {    
        $transactions = DB::table('journal_entry_lines as jl')
            ->join('journal_entries as je', 'jl.journal_entry_id', '=', 'je.id')
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
            ->where('jl.account_id', $accountId)
            ->whereBetween('je.date', [$startDate, $endDate])
            ->orderBy('je.date')
            ->orderBy('jl.id')
            ->get();
    
        return [
            'opening_balance' => $openingBalance,
            'transactions'    => $transactions,
        ];
    }
}
