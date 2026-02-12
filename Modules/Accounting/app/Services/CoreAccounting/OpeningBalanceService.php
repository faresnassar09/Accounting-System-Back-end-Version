<?php

namespace Modules\Accounting\Services\CoreAccounting;

use App\Services\Api\ApiResponseFormatter;
use App\Services\Logging\LoggerService;
use Illuminate\Support\Facades\DB;
use Modules\Accounting\Repositories\Contracts\AccountRepositoryInterface;
use Modules\Accounting\Repositories\Contracts\JournalEntryRepositoryInterface as JournalEntryRepository;

class OpeningBalanceService
{

    public function __construct(

        public ApiResponseFormatter $apiResponseFormatter,
        public JournalEntryRepository $JournalEntryRepository,
        public LoggerService $loggerService,
        public AccountRepositoryInterface $accountRepositoryInterface,

    ) {}

    public function store($data)
    {
        $header = $data->header;
        $lines = collect($data->lines);
        $totalDebit = $lines->sum('debit');
        $totalCredit = $lines->sum('credit');
        $header['total_debit'] = $header['total_credit'] = max($totalDebit, $totalCredit);
        $diffTotals = $totalDebit - $totalCredit;

        DB::transaction(function() use($header,$lines,$diffTotals){

        $journalentryHeader = $this->JournalEntryRepository->store(

            $header,
            $lines,
            'opening'
        );

        if ($diffTotals != 0) {
            $BalanceDiffAccount = $this->accountRepositoryInterface
                ->getOpeningBalanceAccount();

            $this->JournalEntryRepository->storeDiffBalancerLines(

                $journalentryHeader,
                $diffTotals,
                $BalanceDiffAccount->id??1
            );
        }
        }); 

    }
}
