<?php

namespace Modules\Accounting\Queries;

use Illuminate\Support\Facades\DB;
class GetAccountCumulativeBalancesQuery{

     public function __invoke($endAt){

        return DB::table('accounts as a')
        ->join('account_types as at', 'a.account_type_id', '=', 'at.id')
        ->join('journal_entry_lines as ji', 'a.id', '=', 'ji.account_id')
        ->join('journal_entries as je', 'ji.journal_entry_id', '=', 'je.id')
        ->where('je.date', '<', $endAt)
        ->whereIn('at.account_group', ['assets', 'liabilities', 'equity']) 
        ->selectRaw("
        a.id, 
        a.name,
        at.type, 
        CASE 
            WHEN SUM(ji.debit - ji.credit) > 0 THEN SUM(ji.debit - ji.credit) 
            ELSE 0 
        END as debit,
        CASE 
            WHEN SUM(ji.debit - ji.credit) < 0 THEN ABS(SUM(ji.debit - ji.credit)) 
            ELSE 0 
        END as credit
    ")
        ->groupBy('a.id', 'a.name','at.type')
        // ->having('debit', '!=', 0)
        ->get();

    }

}
