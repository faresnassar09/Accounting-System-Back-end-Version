<?php

namespace Modules\Accounting\Repositories\Eloquent;

use Modules\Accounting\Models\Account;
use Modules\Accounting\Repositories\Contracts\TrialBalanceRepositoryInterface;

class TrialBalanceRepository implements TrialBalanceRepositoryInterface{


    public function calculateAccountsTotals($endDate){

        $r =  Account::withSum(['entryLines as total_debit' => function($query) use ($endDate){
 
 
            $query->whereHas('journalEntry', function ($q) use ($endDate) {
                $q->normalJournalEntry()->whereDate('date','<',$endDate);
            });  
         }],'debit')
         
         ->withSum(['entryLines as total_credit' => function($query) use ($endDate){
 
            $query->whereHas('journalEntry', function ($q) use ($endDate) {
                $q->normalJournalEntry()->whereDate('date','<',$endDate);
            }); 
         }],'credit')
         ->withSum(['entryLines as opening_debit' => function($query) use ($endDate){

            $query->whereHas('journalEntry',function($q) use ($endDate){

                $q->openingJournal()->whereDate('date','<',$endDate);

            });


         }],'debit')
         ->withSum(['entryLines as opening_credit' => function($query) use ($endDate){

            $query->whereHas('journalEntry',function($q) use ($endDate){

                $q->openingJournal()->whereDate('date','<',$endDate);

            });


         }],'credit')
         ->get();
 
        //  \Log::info('d',[$r]);

         return $r;
     }

}
