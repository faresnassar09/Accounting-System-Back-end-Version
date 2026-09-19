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
            'branch_id' => $header['branch_id'] ?? null,
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
                'branch_id' => $line['branch_id'] ?? $header->branch_id ?? null,
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

    public function paginate(array $filters = [], int $perPage = 15)
    {
        return JournalEntry::with(['lines.account'])
            ->when($filters['reference'] ?? null, function ($query, $reference) {
                return $query->where('reference', 'like', "%{$reference}%");
            })
            ->when($filters['status'] ?? null, function ($query, $status) {
                return $query->where('status', $status);
            })
            ->when($filters['type'] ?? null, function ($query, $type) {
                return $query->where('type', $type);
            })
            ->when($filters['start_date'] ?? $filters['startDate'] ?? null, function ($query, $startDate) {
                return $query->whereDate('date', '>=', $startDate);
            })
            ->when($filters['end_date'] ?? $filters['endDate'] ?? null, function ($query, $endDate) {
                return $query->whereDate('date', '<=', $endDate);
            })
            ->when($filters['account_id'] ?? null, function ($query, $accountId) {
                return $query->whereHas('lines', function ($q) use ($accountId) {
                    $q->where('account_id', $accountId);
                });
            })
            ->when($filters['branch_id'] ?? $filters['branchId'] ?? null, function ($query, $branchId) {
                return $query->where(function ($q) use ($branchId) {
                    $q->where('branch_id', $branchId)
                      ->orWhereHas('lines', fn ($l) => $l->where('branch_id', $branchId));
                });
            })
            ->orderBy('date', 'desc')
            ->orderBy('id', 'desc')
            ->paginate($perPage);
    }

    public function findById(int $id)
    {
        return JournalEntry::with(['lines.account'])->find($id);
    }

    public function reverse(JournalEntry $entry, ?string $reason = null, ?string $reversalDate = null, ?int $userId = null): JournalEntry
    {
        $date = $reversalDate ?? now()->format('Y-m-d H:i:s');
        $reversalReference = 'REV-' . $entry->reference;

        if (JournalEntry::where('reference', $reversalReference)->exists()) {
            $reversalReference .= '-' . time();
        }

        $description = 'Reversal of Entry #' . $entry->id . ' (' . $entry->reference . ')';
        if (!empty($reason)) {
            $description .= ': ' . $reason;
        }

        $reversingEntry = JournalEntry::create([
            'type' => 'adjustment',
            'reference' => $reversalReference,
            'total_debit' => $entry->total_credit,
            'total_credit' => $entry->total_debit,
            'date' => $date,
            'description' => $description,
            'status' => 'approved',
            'branch_id' => $entry->branch_id,
        ]);

        $sourceType = \Modules\Accounting\Enums\ActorType::USER->value;

        foreach ($entry->lines as $line) {
            $reversingEntry->lines()->create([
                'source_type' => $sourceType,
                'source_reference' => (string) ($userId ?? $line->source_reference ?? 0),
                'account_id' => $line->account_id,
                'branch_id' => $line->branch_id ?? $entry->branch_id,
                'debit' => $line->credit,
                'credit' => $line->debit,
                'date' => $date,
            ]);
        }

        $entry->update(['status' => 'cancled']);

        return $reversingEntry->load('lines.account');
    }
}
