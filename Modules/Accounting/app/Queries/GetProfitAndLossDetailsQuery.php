<?php

namespace Modules\Accounting\Queries;

use Illuminate\Support\Facades\DB;
class GetProfitAndLossDetailsQuery{

     public function __invoke($startDate,$endDate){
        return DB::table('journal_entry_lines as jl')
    ->join('journal_entries as je', 'jl.journal_entry_id', '=', 'je.id')
    ->join('accounts as a', 'jl.account_id', '=', 'a.id')
    ->join('account_types as at', 'a.account_type_id', '=', 'at.id')
    ->whereBetween('je.date', [$startDate, $endDate])
    ->whereIn('at.account_group', ['revenues', 'expenses']) 
    ->select(
        'a.id',
        'a.name',
        'a.number',
        'at.account_group',
        'at.type',
        DB::raw("
        SUM(
CASE 
        WHEN at.type = 'sales_deductions' THEN (jl.debit - jl.credit)
        WHEN at.account_group = 'revenues' THEN (jl.credit - jl.debit) 
        WHEN at.account_group = 'expenses' THEN (jl.debit - jl.credit) 
        ELSE (jl.debit - jl.credit) 
    END
        ) as balance
    ")
    )
    ->groupBy('a.id', 'a.name', 'a.number', 'at.account_group','at.type')
    ->orderBy('at.account_group', 'desc') 
    ->get(); 
     }
    }  