<?php

namespace Modules\Accounting\Services\CoreAccounting;

use App\Services\Api\ApiResponseFormatter;
use App\Services\Logging\LoggerService;
use Illuminate\Support\Facades\DB;
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
        $lines = collect($data->lines);
        
        DB::transaction(function () use ($header, $lines) {

            $this->journalInterface->store($header, $lines);
        });

        return true;
    }
}
