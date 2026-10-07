<?php

namespace Modules\Accounting\Repositories\Eloquent;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Modules\Accounting\Models\RecurringJournalEntry;
use Modules\Accounting\Repositories\Contracts\RecurringJournalEntryRepositoryInterface;

class RecurringJournalEntryRepository implements RecurringJournalEntryRepositoryInterface
{
    public function getEntries(?string $status, ?int $branchId): Collection
    {
        return RecurringJournalEntry::with(['lines.account', 'branch'])
            ->when($status, fn ($q) => $q->where('status', $status))
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->orderBy('id', 'desc')
            ->get();
    }

    public function getEntryById(int $id): RecurringJournalEntry
    {
        return RecurringJournalEntry::with(['lines.account', 'branch', 'generatedEntries'])->findOrFail($id);
    }

    public function createEntry(array $data, array $lines): RecurringJournalEntry
    {
        return DB::transaction(function () use ($data, $lines) {
            $recurring = RecurringJournalEntry::create($data);

            foreach ($lines as $line) {
                $recurring->lines()->create([
                    'account_id'  => $line['account_id'],
                    'debit'       => $line['debit'],
                    'credit'      => $line['credit'],
                    'description' => $line['description'] ?? null,
                    'branch_id'   => $line['branch_id'] ?? $recurring->branch_id,
                ]);
            }

            return $recurring;
        });
    }

    public function updateEntry(int $id, array $data): RecurringJournalEntry
    {
        $entry = RecurringJournalEntry::findOrFail($id);
        $entry->update($data);
        return $entry;
    }

    public function deleteEntry(int $id): bool
    {
        $entry = RecurringJournalEntry::findOrFail($id);
        $entry->lines()->delete();
        return $entry->delete();
    }
}
