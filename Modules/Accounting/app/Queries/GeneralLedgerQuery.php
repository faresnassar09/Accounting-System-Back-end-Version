<?php

namespace Modules\Accounting\Queries;

use Illuminate\Support\Facades\DB;

class GeneralLedgerQuery{

    public function __invoke($openingBalance,$accountId, $startDate, $endDate)
    {    
        $transactions = DB::table('journal_entry_lines as jl')
            ->join('journal_entries as je', 'jl.journal_entry_id', '=', 'je.id')
            ->select(
                'je.date',
                'je.reference',
                'je.description',
                'jl.debit',
                'jl.credit',
                DB::raw("$openingBalance + SUM(jl.debit - jl.credit) OVER (ORDER BY je.date, jl.id) as running_balance")
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
