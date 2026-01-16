<?php

namespace Modules\Accounting\Repositories\Eloquent;

use Modules\Accounting\Models\Account;
use Modules\Accounting\Repositories\Contracts\BalanceSheetRepositoryInterface;

class BalanceSheetRepository implements BalanceSheetRepositoryInterface {


    public function getAccountsWithTotals($endDate){

        return Account::whereHas('accountType',function($query){

            $query->whereIn('account_group',['assets', 'liabilities', 'equity']);

        })
        ->withSum(['entryLines as total_debit' => function ($query) use($endDate){

            $query->whereHas('journalEntry', function ($q) use ($endDate) {
                $q->whereDate('date','<=',$endDate);
            }); 

        }],'debit')
        ->withSum(['entryLines as total_credit' => function ($query) use($endDate){

            $query->whereHas('journalEntry', function ($q) use ($endDate) {
                $q->whereDate('date','<=',$endDate);
            }); 
        
        
        }],'credit')
        ->get();

    }
    
}