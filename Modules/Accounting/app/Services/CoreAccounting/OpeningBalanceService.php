<?php

namespace Modules\Accounting\Services\CoreAccounting;

use App\Services\Api\ApiResponseFormatter;
use App\Services\Logging\LoggerService;
use Illuminate\Support\Facades\DB;
use Modules\Accounting\Enums\ActorType;
use Modules\Accounting\Repositories\Contracts\AccountingMappingRepositoryInterface;
use Modules\Accounting\Repositories\Contracts\AccountRepositoryInterface;
use Modules\Accounting\Repositories\Contracts\JournalEntryRepositoryInterface as JournalEntryRepository;

class OpeningBalanceService
{

    public function __construct(

        public ApiResponseFormatter $apiResponseFormatter,
        public JournalEntryRepository $JournalEntryRepository,
        public LoggerService $loggerService,
        public AccountRepositoryInterface $accountRepositoryInterface,
        public AccountingMappingRepositoryInterface $accountingMapping,

    ) {}

    public function store($data)
    {

        $userId = current_guard_user()->id;

        $header = $data->header;
        $lines = collect($data->lines)->map(function ($line) use ($userId) {

            $line['source_reference'] = $userId;

            return $line;
        });
        $totalDebit = $lines->sum('debit');
        $totalCredit = $lines->sum('credit');
        $header['total_debit'] = $header['total_credit'] = max($totalDebit, $totalCredit);
        $diffTotals = $totalDebit - $totalCredit;
        $actorType = ActorType::USER->value;
        

        DB::transaction(function() use($actorType, $userId, $header,$lines,$diffTotals){

        $journalentryHeader = $this->JournalEntryRepository->store(

            $actorType,
            $header,
            $lines,
            'opening'
        );

        if ($diffTotals != 0) {
            $BalanceDiffAccount = $this->accountingMapping
                ->getOpeningBalanceAccount();

            $this->JournalEntryRepository->storeDiffBalancerLines(

                $actorType,
                $userId,
                $journalentryHeader,
                $diffTotals,
                $BalanceDiffAccount->id
            );
        }
        }); 

    }
}
