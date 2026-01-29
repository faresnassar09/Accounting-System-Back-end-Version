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


    public function store($header,$lines,$type='journal')
    {
        $journalentryHeader = JournalEntry::create([

            'user_id' => current_guard_user()->id,
            'type' => $type,
            'reference' => $header['reference'],
            'total_credit' => $header['total_credit'],
            'total_debit' => $header['total_debit'],
            'date' => $header['date'] ?? now(),
            'description' => $header['description'],
        ]);

        $this->storeLines($journalentryHeader, $lines);

        return $journalentryHeader;

    }

    public function storeLines($header,$lines)
    {

        foreach ($lines as $line) {

            $header->lines()->create([

                'account_id' => $line['account_id'],
                'credit' => $line['credit'] ?? 0.00,
                'debit' => $line['debit'] ?? 0.00,
            ]);
        }
    }

    public function storeDiffBalancerLines($JournalEntry, $diffTotals,$BalancerAccountId){


            $JournalEntry->lines()->create([

                'account_id' => $BalancerAccountId,
                'debit' => $diffTotals < 0 ? abs($diffTotals) : 0.00,
                'credit' => $diffTotals >  0 ? abs($diffTotals) : 0.00,
            ]);
        }

    }
