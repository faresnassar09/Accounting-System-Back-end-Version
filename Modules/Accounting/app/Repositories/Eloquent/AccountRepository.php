<?php 

namespace Modules\Accounting\Repositories\Eloquent;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\DB;
use Modules\Accounting\Models\Account;
use Modules\Accounting\Repositories\Contracts\AccountRepositoryInterface as AccountingChartInterface;

class AccountRepository implements AccountingChartInterface{


    public function getAllAccounts(){

        return Account::all(['id','name','number']);

    }

    public function findAccount($accountId)
    {
       return Account::select('id','name','number')->find($accountId);

    }

    public function getClosingAccounts(){

        $account =  Account::whereHas('accountType',function($query){

            $query->whereIn('type',['equity_capital','retained_earnings']);

        })->get(['id','name','number']);

        return $account;

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