<?php

namespace Modules\Accounting\Repositories\Eloquent;


use Modules\Accounting\Models\JournalEntry;
use Modules\Accounting\Repositories\Contracts\JournalEntryRepositoryInterface;

class JournalEntryRepository implements JournalEntryRepositoryInterface
{


    public function store($data)
    {

        $journalEntryHeader = $data->header;
        $journalEntryLines =  $data->lines;

        $journalentry = JournalEntry::create([

            'user_id' => current_guard_user()->id,
            'reference' => $journalEntryHeader['reference'],
            'total_credit' => $journalEntryHeader['total_credit'],
            'total_debit' => $journalEntryHeader['total_debit'],
            'date' => $journalEntryHeader['date'] ?? now(),
            'description' => $journalEntryHeader['description'],
        ]);

        $this->storeLines($journalentry, $journalEntryLines);
    }


    public function storeLines($JournalEntry, $lines)
    {

        foreach ($lines as $line) {

            $JournalEntry->lines()->create([

                'account_id' => $line['account_id'],
                'credit' => $line['credit'] ?? 0.00,
                'debit' => $line['debit'] ?? 0.00,
                'description' => $line['description'] ?? '',
                'created_at' => $JournalEntry->date,
            ]);
        }
    }

}
