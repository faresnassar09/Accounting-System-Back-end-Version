<?php

namespace Modules\Accounting\Services\CoreAccounting;

use App\Services\Api\ApiResponseFormatter;
use App\Services\Logging\LoggerService;
use Illuminate\Support\Facades\Auth;
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

        try {

            
        $header = $data->header;
        $lines = collect($data->lines);
        $totalAmount = max($lines->sum('debit'),$lines->sum('credit'));

            $balanced = $this->checkJournaliBalanced($lines);


            if (!$balanced) {

                return $this->apiResponseFormatter->failedResponse(

                    'Journal Entry Total Credit And Debit are not balanced',
                    [],
                    422
                );
            
            }

            $this->journalInterface->store($header,$lines,$totalAmount);

            return $this->apiResponseFormatter->successResponse(

                'Journal Entry Saved Successfully',
                [],

            );


        } catch (\Exception $e) {

            $this->loggerService->failedLogger(
                'Error Occurred While Saving Journal Entry',
                [],
                $e->getMessage()

            );

            return $this->apiResponseFormatter->failedResponse(

                'Error Occurred While Saving Journal Entry',
                [],

            );
        }
    }



    public function checkJournaliBalanced($lines)
    {


        if ($lines->sum('debit') === $lines->sum('credit')) {

            return true;
        }

        return false;
    }
}
