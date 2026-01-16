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

            $balancing = $this->checkJournalisBalanced($data->header);

            if (!$balancing) {

                return $this->apiResponseFormatter->failedResponse(

                    'Journal Entry Total Credit And Debit are not balanced',
                    [],
                    422
                );
            
            }

            $this->journalInterface->store($data);

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



    public function checkJournalisBalanced($journal)
    {


        if ($journal['total_debit'] === $journal['total_credit']) {

            return true;
        }

        return false;
    }
}
