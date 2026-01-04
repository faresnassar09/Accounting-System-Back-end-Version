<?php

namespace Modules\Accounting\Repositories\Eloquent;

use Modules\Accounting\Models\Account;
use Modules\Accounting\Repositories\Contracts\TrialBalanceRepositoryInterface;

class TrialBalanceRepository implements TrialBalanceRepositoryInterface{


    public function calculateAccountsTotals($endDate){

        return Account::withSum(['entryLines as total_debit' => function($query) use ($endDate){
 
 
            $query->whereHas('journalEntry', function ($q) use ($endDate) {
                $q->whereDate('date','<',$endDate);
            });  
         }],'debit')
         
         ->withSum(['entryLines as total_credit' => function($query) use ($endDate){
 
            $query->whereHas('journalEntry', function ($q) use ($endDate) {
                $q->whereDate('date','<',$endDate);
            }); 
         }],'credit')
         ->get();
 
     }

}
