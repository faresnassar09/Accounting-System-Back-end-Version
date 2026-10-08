<?php

namespace Modules\Accounting\Repositories\Contracts;

use Modules\Accounting\Models\RecurringJournalEntry;
use Illuminate\Database\Eloquent\Collection;

interface RecurringJournalEntryRepositoryInterface
{
    public function getEntries(?string $status, ?int $branchId): Collection;
    public function getEntryById(int $id): RecurringJournalEntry;
    public function createEntry(array $data, array $lines): RecurringJournalEntry;
    public function updateEntry(int $id, array $data): RecurringJournalEntry;
    public function deleteEntry(int $id): bool;
}
