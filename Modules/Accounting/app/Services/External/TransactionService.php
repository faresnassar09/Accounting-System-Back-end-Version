<?php

namespace Modules\Accounting\Services\External;

use Modules\Accounting\Adapters\Contracts\ExternalTransactionAdapterInterface;
use Modules\Accounting\Enums\ActorType;
use Modules\Accounting\Repositories\Contracts\AccountingMappingRepositoryInterface;
use Modules\Accounting\Repositories\Contracts\JournalEntryRepositoryInterface;

class TransactionService {

    public function __construct(
        public ExternalTransactionAdapterInterface $externalAdapterInterface,
        public JournalEntryRepositoryInterface $journal,
        public AccountingMappingRepositoryInterface $accounting,
        ){}
    public function create($data){

        $sourceType = 'external_service';
        
        $journalEntry = $this->externalAdapterInterface->transformToJournal($data);

        $header = $journalEntry['header'];
        $lines = $journalEntry['lines'];

        $sourceType = ActorType::EXTERNAL->value; 
        $this->journal->store($sourceType,$header,$lines);

        

    }

    public function getTransactions($data){

        $sourceReference = $data['source_reference'] ?? null;
        $startDate = $data['start_date'] ?? null ;
        $endDate = $data['end_date'] ?? null;

        $start = ($startDate === 'null' || empty($startDate)) ? null : $startDate;
        $end = ($endDate === 'null' || empty($endDate)) ? null : $endDate;

        return $this->journal->getTransactions($sourceReference,$start,$end);
    }

    // public function getTransactionsForSource($data){

    //     $startDate = $data['start_date'];
    //     $endDate = $data['end_date'];
    //     $sourceReference = $data['source_reference'];

    //     $start = ($startDate === 'null' || empty($startDate)) ? null : $startDate;
    //     $end = ($endDate === 'null' || empty($endDate)) ? null : $endDate;


    //     return $this->journal->getTransactions($sourceReference,$start,$end);
    // }
    
}
