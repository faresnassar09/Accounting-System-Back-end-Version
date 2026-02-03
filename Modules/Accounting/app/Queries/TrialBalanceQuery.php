<?php

namespace Modules\Accounting\Queries;

use Modules\Accounting\Models\Account;

class TrialBalanceQuery{


    public function __invoke($startOfYear,$endDate){
  
        return Account::query()
        ->leftJoin('journal_entry_lines as ji', 'accounts.id', '=', 'ji.account_id')
        ->join('journal_entries as je', function($join) use ($startOfYear, $endDate) {
            $join->on('ji.journal_entry_id', '=', 'je.id')
                 ->whereBetween('je.date', [$startOfYear, $endDate]);
        })
        ->select('accounts.id', 'accounts.name','accounts.number')
        ->selectRaw("
            SUM(CASE WHEN je.type != 'opening' THEN ji.debit ELSE 0 END) as total_debit,
            SUM(CASE WHEN je.type != 'opening' THEN ji.credit ELSE 0 END) as total_credit,
            SUM(CASE WHEN je.type = 'opening' THEN ji.debit ELSE 0 END) as opening_debit,
            SUM(CASE WHEN je.type = 'opening' THEN ji.credit ELSE 0 END) as opening_credit
            
        ")
        ->groupBy('accounts.id', 'accounts.name','accounts.number')
        ->get();
     }

}
