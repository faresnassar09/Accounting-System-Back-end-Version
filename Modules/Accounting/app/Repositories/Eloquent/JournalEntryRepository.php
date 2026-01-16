<?php

namespace Modules\Accounting\Repositories\Eloquent;


use Modules\Accounting\Models\JournalEntry;
use Modules\Accounting\Repositories\Contracts\JournalEntryRepositoryInterface;
use Modules\Accounting\Repositories\Contracts\AccountRepositoryInterface;
class JournalEntryRepository implements JournalEntryRepositoryInterface
{

    public function __construct(public AccountRepositoryInterface $accountRepositoryInterface)
    {
    }


    public function store($data)
    {

        $journalEntryHeader = $data->header;
        $journalEntryLines = collect($data->lines);

        $journalentry = JournalEntry::create([

            'user_id' => current_guard_user()->id,
            'reference' => $journalEntryHeader['reference'],
            'total_credit' => $journalEntryLines->sum('credit'),
            'total_debit' => $journalEntryLines->sum('debit'),
            'date' => $journalEntryHeader['date'] ?? now(),
            'description' => $journalEntryHeader['description'],
        ]);

        $this->storeLines($journalentry, $journalEntryLines);
    }


    public function storeOpeningBalanceEntry($data){

        $journalEntryHeader = $data->header;
        $journalEntryLines = collect($data->lines);

        //Apply The Result On Total Debit & Total Credit To Balance The Journal

        $totalDebitAndCredit =
        $journalEntryLines->sum('debit') +
        $journalEntryLines->sum('credit');

        $journalentry = JournalEntry::create([

            'user_id' => current_guard_user()->id,
            'reference' => $journalEntryHeader['reference'],
            'type' => 'opening',         
            'total_credit' =>$totalDebitAndCredit,
            'total_debit' => $totalDebitAndCredit,
            'date' => $journalEntryHeader['date'] ?? now(),
            'description' => $journalEntryHeader['description'],
        ]);

        $this->storeLines($journalentry, $journalEntryLines);
        $this->storeBalanceDiffLines($journalentry,$journalEntryLines);

    }

    private function storeLines($JournalEntry, $lines)
    {

        foreach ($lines as $line) {

            $JournalEntry->lines()->create([

                'account_id' => $line['account_id'],
                'credit' => $line['credit'] ?? 0.00,
                'debit' => $line['debit'] ?? 0.00,
                'description' => $line['description'] ?? '',
            ]);
        }
    }

    private function storeBalanceDiffLines($JournalEntry, $lines){

        $BalanceDiffAccount = $this->accountRepositoryInterface->getOpeningBalanceAccount();

        foreach ($lines as $line) {

        $debit  = $line['debit'] ;
        $credit = $line['credit'] ;

            $JournalEntry->lines()->create([

                'account_id' => $BalanceDiffAccount->id,
                'debit' => $debit <= 0 ? $credit : 0.00,
                'credit' => $credit >= 0 ? $debit : 0.00,
                'description' => $line['description'] ?? '',
            ]);
        }

    }


}
