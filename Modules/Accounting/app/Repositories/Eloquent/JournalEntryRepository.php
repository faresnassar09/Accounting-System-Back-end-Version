<?php

namespace Modules\Accounting\Repositories\Eloquent;


use Modules\Accounting\Models\JournalEntry;
use Modules\Accounting\Models\JournalEntryLine;
use Modules\Accounting\Repositories\Contracts\JournalEntryRepositoryInterface;
use Modules\Accounting\Repositories\Contracts\AccountRepositoryInterface;

class JournalEntryRepository implements JournalEntryRepositoryInterface
{

    public function __construct(public AccountRepositoryInterface $accountRepositoryInterface) {}


    public function store($surceType, $header, $lines, $type = 'journal')
    {
        $journalentryHeader = JournalEntry::create([

            'type' => $type,
            'reference' => $header['reference'],
            'total_credit' => $header['total_credit'],
            'total_debit' => $header['total_debit'],
            'date' => $header['date'] ?? now(),
            'description' => $header['description'],
        ]);

        $this->storeLines($journalentryHeader, $lines, $surceType);

        return $journalentryHeader;
    }

    public function storeLines($header, $lines, $surceType)
    {

        foreach ($lines as $line) {

            $header->lines()->create([

                'source_type' => $surceType,
                'source_reference' => $line['source_reference'],
                'account_id' => $line['account_id'],
                'credit' => $line['credit'] ?? 0.00,
                'debit' => $line['debit'] ?? 0.00,
                'date' => $header->date,
            ]);
        }
    }

    public function storeDiffBalancerLines($sourceType, $actorId, $JournalEntry, $diffTotals, $BalancerAccountId)
    {


        $JournalEntry->lines()->create([

            'source_type' => $sourceType,
            'source_reference' => $actorId,
            'account_id' => $BalancerAccountId,
            'debit' => $diffTotals < 0 ? abs($diffTotals) : 0.00,
            'credit' => $diffTotals >  0 ? abs($diffTotals) : 0.00,
        ]);
    }

    public function getTransactions($sourceReference, $startDate, $endDate)
    {

        return JournalEntryLine::when($startDate, function ($query, $startDate) {

                return $query->whereDate('date', '>=', $startDate);
            })
            ->when($endDate, function ($query, $endDate) {

                return $query->whereDate('date', '<=', $endDate);
            })->when($sourceReference, function ($query, $sourceReference) {

                return $query->where('source_reference', $sourceReference);
            })
            ->get();
    }


}
