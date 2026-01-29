<?php

namespace Modules\Accounting\Repositories\Contracts;


interface JournalEntryRepositoryInterface {

public function store($header,$lines,$type ='journal');
public function storeLines($header,$lines);
public function storeDiffBalancerLines($JournalEntry, $diffTotals,$BalancerAccountId);

}