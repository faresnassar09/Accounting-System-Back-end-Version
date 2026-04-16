<?php

namespace Modules\Accounting\Services\CoreAccounting;

use App\Services\Api\ApiResponseFormatter;
use App\Services\Logging\LoggerService;
use Illuminate\Support\Facades\DB;
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


    public function store($data)
    {

        $header = $data->header;
        $entryLines = collect($data->lines);
        $actorType = ActorType::USER->value;

      $lines =  $entryLines->map(function ($line) {


             $line['source_reference'] = current_guard_user()->id;

             return $line;
        });

        
        DB::transaction(function () use ($actorType, $header, $lines) {

            $this->journalInterface->store($actorType,$header, $lines);
        });

        return true;
    }
}
