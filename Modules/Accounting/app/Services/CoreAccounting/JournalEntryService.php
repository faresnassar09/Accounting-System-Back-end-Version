<?php

namespace Modules\Accounting\Services\CoreAccounting;

use App\Services\Api\ApiResponseFormatter;
use App\Services\Logging\LoggerService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Modules\Accounting\Enums\ActorType;
use Modules\Accounting\Repositories\Contracts\AccountRepositoryInterface as ChartInterface;
use Modules\Accounting\Repositories\Contracts\JournalEntryRepositoryInterface as JournalInterface;

class JournalEntryService
{

    private $totalDebit;
    private $totalCredit;

    public function __construct(
        public JournalInterface $journalInterface,
        public ChartInterface $chartInterface,
        public ApiResponseFormatter $apiResponseFormatter,
        public LoggerService $loggerService,
    ) {}


    public function store(array $data, ?int $userId = null)
    {


        $journalHeader = $this->calculateTheTotals($data)['journalHeader'];


            throw_if(!$this->checkLinesAreBalanced());


     
        $entryLines = collect($data['lines']);

        $actorType = ActorType::USER->value;

        $lines = $entryLines->map(function ($line) use ($userId) {
            $line['source_reference'] = $userId ?? 0;

            return $line;
        });


        DB::transaction(function () use ($actorType, $journalHeader, $lines) {

            $this->journalInterface->store($actorType, $journalHeader, $lines);
        });

        return true;
    }


    private function calculateTheTotals($data)
    {

        $this->totalDebit = $data['journalHeader']['total_debit'] = collect($data['lines'])->sum('debit');
        $this->totalCredit = $data['journalHeader']['total_credit'] = collect($data['lines'])->sum('credit');


        return $data;
    }

    private function checkLinesAreBalanced()
    {

        if ($this->totalDebit == $this->totalCredit) {

            return true;
        } else {
            return false;
        }
    }
}
