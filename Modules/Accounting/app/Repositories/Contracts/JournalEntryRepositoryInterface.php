<?php

namespace Modules\Accounting\Repositories\Contracts;


interface JournalEntryRepositoryInterface {

public function store($data);
public function storeOpeningBalanceEntry($data);

}