<?php

namespace Modules\Accounting\Services\CoreAccounting;

use App\Services\Api\ApiResponseFormatter;
use App\Services\Logging\LoggerService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Modules\Accounting\Enums\ActorType;
use Modules\Accounting\Repositories\Contracts\AccountRepositoryInterface as ChartInterface;
use Modules\Accounting\Repositories\Contracts\JournalEntryRepositoryInterface as JournalInterface;

use Carbon\Carbon;
use Modules\Accounting\Repositories\Contracts\FinancialClosingReposiroryInterface;

class JournalEntryService
{

    private $totalDebit;
    private $totalCredit;

    public function __construct(
        public JournalInterface $journalInterface,
        public ChartInterface $chartInterface,
        public ApiResponseFormatter $apiResponseFormatter,
        public LoggerService $loggerService,
        public FinancialClosingReposiroryInterface $financialClosedRepository,
    ) {}

    public function getJournalEntries(array $filters = [], int $perPage = 15)
    {
        return $this->journalInterface->paginate($filters, $perPage);
    }

    public function getJournalEntry(int $id)
    {
        $entry = $this->journalInterface->findById($id);

        if (!$entry) {
            throw new \Illuminate\Database\Eloquent\ModelNotFoundException("Journal Entry with ID {$id} not found.");
        }

        return $entry;
    }

    public function reverse(int $id, ?string $reason = null, ?string $reversalDate = null, ?int $userId = null)
    {
        $entry = $this->journalInterface->findById($id);

        if (!$entry) {
            throw new \Illuminate\Database\Eloquent\ModelNotFoundException("Journal Entry with ID {$id} not found.");
        }

        if ($entry->status === 'cancled') {
            throw new \DomainException("Journal Entry #{$entry->id} ({$entry->reference}) is already canceled/reversed.");
        }

        $originalYear = Carbon::parse($entry->date)->format('Y');
        if ($this->financialClosedRepository->isYearClosed($originalYear)) {
            throw new \DomainException("Cannot reverse Journal Entry from closed financial year ({$originalYear}).");
        }

        $revDate = $reversalDate ?? now()->format('Y-m-d H:i:s');
        $reversalYear = Carbon::parse($revDate)->format('Y');
        if ($this->financialClosedRepository->isYearClosed($reversalYear)) {
            throw new \DomainException("Cannot post reversal into closed financial year ({$reversalYear}).");
        }

        return DB::transaction(function () use ($entry, $reason, $revDate, $userId) {
            return $this->journalInterface->reverse($entry, $reason, $revDate, $userId);
        });
    }


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
