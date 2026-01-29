<?php

namespace Modules\Accounting\Queries;

use Illuminate\Support\Facades\DB;

class GetOpeningBalanceQuery{

    public function __invoke( $accountId, $startDate)
    {
        $lastOpening = DB::table('journal_entry_lines as jl')
            ->join('journal_entries as je', 'jl.journal_entry_id', '=', 'je.id')
            ->whereIn('jl.account_id', $accountId)
            ->where('je.type', 'opening')
            ->where('je.date', '<=', $startDate)
            ->orderBy('je.date', 'desc')
            ->select(DB::raw('SUM(debit - credit) as balance'), 'je.date')
            ->groupBy('je.date')
            ->first();
    
        $balance = $lastOpening ? $lastOpening->balance : 0;
        $dateFrom = $lastOpening ? $lastOpening->date : '1970-01-01';
    
        $extra = DB::table('journal_entry_lines as jl')
            ->join('journal_entries as je', 'jl.journal_entry_id', '=', 'je.id')
            ->whereIn('jl.account_id', $accountId)
            ->where('je.type', '!=', 'opening')
            ->whereBetween('je.date', [$dateFrom, \Carbon\Carbon::parse($startDate)->subSecond()])
            ->sum(DB::raw('debit - credit'));
    
        return (float) ($balance + $extra);
    }
}
