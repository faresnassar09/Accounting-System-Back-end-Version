<?php 

namespace Modules\Accounting\Repositories\Eloquent;

use Illuminate\Support\Facades\DB;
use Modules\Accounting\Models\ClosedFinancialYear;
use Modules\Accounting\Repositories\Contracts\FinancialClosingReposiroryInterface;

class FinancialClosingRepository implements FinancialClosingReposiroryInterface{

    public function __construct(){}

    public function isYearClosed($year){

        return ClosedFinancialYear::where('year',$year)
        ->first();

    }
    
    public function flagYearAsClosed($year,$netProfit,$clogingAccountId){

        ClosedFinancialYear::create([

            'closed_by' => current_guard_user()->id,
            'year' => $year,
            'net_profit_loss' => $netProfit,
            'retained_earnings_account_id' => $clogingAccountId, 

        ]);

    }

    
    public function getFinalBalancesForClosing($endAt){

        return DB::table('accounts as a')
        ->join('account_types as at', 'a.account_type_id', '=', 'at.id')
        ->join('journal_entry_lines as ji', 'a.id', '=', 'ji.account_id')
        ->join('journal_entries as je', 'ji.journal_entry_id', '=', 'je.id')
        ->where('je.date', '<', $endAt)
        ->whereIn('at.account_group', ['assets', 'liabilities', 'equity']) 
        ->selectRaw("
        a.id, 
        a.name,
        CASE 
            WHEN SUM(ji.debit - ji.credit) > 0 THEN SUM(ji.debit - ji.credit) 
            ELSE 0 
        END as debit,
        CASE 
            WHEN SUM(ji.debit - ji.credit) < 0 THEN ABS(SUM(ji.debit - ji.credit)) 
            ELSE 0 
        END as credit
    ")
        ->groupBy('a.id', 'a.name')
        // ->having('debit', '!=', 0)
        ->get();

    }  
}