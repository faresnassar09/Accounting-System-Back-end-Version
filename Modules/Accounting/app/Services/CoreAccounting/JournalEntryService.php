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

    public function __construct(
        public JournalInterface $journalInterface,
        public ChartInterface $chartInterface,
        public ApiResponseFormatter $apiResponseFormatter,
        public LoggerService $loggerService,
    ) {}


    public function store(array $data, ?int $userId = null)
    {

        $journalHeader = $data['journalHeader'];
        $entryLines = collect($data['lines']);
        $actorType = ActorType::USER->value;

        $lines = $entryLines->map(function ($line) use ($userId) {
            $line['source_reference'] = $userId ?? 0;

            return $line;
        });

        
        DB::transaction(function () use ($actorType, $journalHeader, $lines) {

            $this->journalInterface->store($actorType,$journalHeader, $lines);
        });

        return true;
    }
}
