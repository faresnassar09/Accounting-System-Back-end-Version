<?php 

namespace Modules\Accounting\Repositories\Eloquent;

use Modules\Accounting\Repositories\Contracts\AccountRepositoryInterface as AccountingChartInterface;
use Illuminate\Support\Facades\DB;

use Modules\Accounting\Models\Account;

class AccountRepository implements AccountingChartInterface{


    public function getAccounts(){

        return Account::all(['id','name','number']);

    }

    public function getAccount($accountId)
    {
       return Account::select('id','name','number')->find($accountId);

    }

    public function getOpeningBalanceAccount(){

        return Account::whereHas('accountType',function($query){

            $query->where('type','opening_balance_diff');
        })->first();
        
    }

    public function chartTree( )
    {
       $accounts = Account::whereNull('parent_id')
        ->with('descendants','accountType:id,type')
        ->get();
        
    return $accounts;
    }

    public function getDebitAndCreditTotals(){

     return Account::withSum('accountEntryLines', 'credit')
        ->withSum('accountEntryLines', 'debit')
        ->get()
        ->map(function ($account) {
            $account->balance = $account->initial_balance
                + ($account->account_entry_lines_sum_debit ?? 0)
                - ($account->account_entry_lines_sum_credit ?? 0);
            return $account;
        });

    }

    public function findRootAccount($id)
    {

        $rawResult = DB::selectOne("
       with recursive parents as (
           select * from accounts where id = ?
           union all
           select a.* from accounts a
           inner join parents p on a.id = p.parent_id
       )
       select * from parents order by parent_id is null desc limit 1
   ", [$id]);

   if ($rawResult) {
    return Account::hydrate([$rawResult])->first();
}

    }

}