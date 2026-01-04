<?php

namespace Modules\Accounting\Repositories\Eloquent;

use Illuminate\Support\Facades\DB;
use Modules\Accounting\Models\Account;
use Modules\Accounting\Repositories\Contracts\GeneralLdegerRepositoryInterface;

class GeneralLedgerRepository implements GeneralLdegerRepositoryInterface{


    
    public function getOpeningBalance($accountId, $startDate)
    {


        $getopningBalance = Account::withSum([
            'entryLines as total_debit' => function ($query) use ($startDate) {
                $query->whereHas('journalEntry', function ($q) use ($startDate) {
                    $q->whereDate('date','<',$startDate);
                });
            }
        ], 'debit')
            ->withSum([
                'entryLines as total_credit' => function ($query) use ($startDate) {
                    $query->whereHas('journalEntry', function ($q) use ($startDate) {
                        $q->whereDate('date','<',$startDate);
                    });
                }
            ], 'credit')



            ->find($accountId);

        return $getopningBalance?->total_debit ?? 0 - $getopningBalance?->total_credit ?? 0;
    }


    public function getAccountPeriodTotals($accountId, $startDate, $endDate)
    {


        $getopningBalance = Account::withSum(['entryLines as total_debit' =>
        
        function ($query) use ($startDate, $endDate) {

            $query->whereHas('journalEntry', function ($q) use ($startDate,$endDate) {
                $q->whereBetween('created_at', [$startDate, $endDate]);
            });
        }], 'debit')


            ->withSum(['entryLines as total_credit' =>
             function ($query) use ($startDate, $endDate) {

                $query->whereHas('journalEntry', function ($q) use ($startDate,$endDate) {
                    $q->whereBetween('created_at', [$startDate, $endDate]);
                });

            }], 'credit')
            ->find($accountId);

        return [

            'total_debit' => $getopningBalance?->total_debit ?? 0,
            'total_credit' =>  $getopningBalance?->total_credit ?? 0];
    }

    public function getTransactions($accountId,$startDate,$endDate){

        $transactions = DB::table('journal_entry_lines AS lines')
        ->join('journal_entries AS entries', 'lines.journal_entry_id', '=', 'entries.id')
        
        ->selectRaw('
            lines.created_at,
            lines.debit,
            lines.credit,
            entries.reference,                     
            entries.description,
            entries.date,

            SUM(lines.debit - lines.credit) 
                OVER (
                    PARTITION BY lines.account_id 
                    ORDER BY lines.created_at, lines.id
                ) AS running_balance
        ')
        
        ->where('lines.account_id', $accountId)
        ->whereBetween('entries.date', [$startDate, $endDate])
        ->orderBy('entries.date')
        ->orderBy('lines.id')
        ->get();


        return $transactions;
    }


}
