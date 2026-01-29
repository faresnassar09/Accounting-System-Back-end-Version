<?php

namespace Modules\Accounting\Queries;

use Illuminate\Support\Facades\DB;
class GetProfitAndLossTotalsQuery{


    public function __invoke($startDate,$endDate){
  
        return DB::table('journal_entry_lines as jl')
            ->join('journal_entries as je', 'jl.journal_entry_id', '=', 'je.id')
            ->join('accounts as a', 'jl.account_id', '=', 'a.id')
            ->join('account_types as at', 'a.account_type_id', '=', 'at.id')
            ->whereBetween('je.date', [$startDate, $endDate])
            ->where('je.type','!=','closing')
            ->selectRaw("
                SUM(CASE WHEN at.account_group = 'revenues' THEN (jl.credit - jl.debit) ELSE 0 END) as total_revenues,
                SUM(CASE WHEN at.account_group = 'expenses' THEN (jl.debit - jl.credit) ELSE 0 END) as total_expenses,
                SUM(CASE WHEN at.account_group = 'cogs' THEN (jl.debit - jl.credit) ELSE 0 END) as total_cogs
            ")
            ->first();  
     }
    }  