<?php

namespace Modules\Accounting\Repositories\Contracts;


interface JournalEntryRepositoryInterface {

    public function store($surceType, $header, $lines, $type = 'journal');
    public function storeLines($header, $lines, $surceType);
    public function storeDiffBalancerLines($sourceType, $actorId, $JournalEntry, $diffTotals, $BalancerAccountId);
    public function getTransactions($sourceReference, $startDate, $endDate);
    public function paginate(array $filters = [], int $perPage = 15);
    public function findById(int $id);
    public function reverse(\Modules\Accounting\Models\JournalEntry $entry, ?string $reason = null, ?string $reversalDate = null, ?int $userId = null): \Modules\Accounting\Models\JournalEntry;
}