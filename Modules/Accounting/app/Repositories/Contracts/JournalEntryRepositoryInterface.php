<?php

namespace Modules\Accounting\Repositories\Contracts;


interface JournalEntryRepositoryInterface {

public function store($surceType,$header,$lines,$type ='journal');
public function storeLines($header,$lines,$surceType);
public function storeDiffBalancerLines($sourceType , $actorId , $JournalEntry, $diffTotals,$BalancerAccountId);
public function getTransactions($sourceReference,$startDate,$endDate);
// public function getTransactionsForSource($sourceReference,$startDate,$endDate);

}