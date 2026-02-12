<?php

namespace Modules\Accounting\Http\Controllers\CoreAccounting;

use App\Http\Controllers\Controller;
use App\Services\Api\ApiResponseFormatter;
use App\Services\Logging\LoggerService;
use Modules\Accounting\Http\Requests\JournalEntryRequest;
use Modules\Accounting\Services\CoreAccounting\JournalEntryService;

class JournalEntriesController extends Controller
{

    public function __construct(

        public JournalEntryService $journalEntryService,
        public ApiResponseFormatter $apiResponseFormatter,
        public LoggerService $loggerService,
        
        ){}

    /**
   * 
   * store normal journal entry 
   *
   * @group journal entry
   */


    public function store(JournalEntryRequest $data) {

        try{

         $this->journalEntryService->store($data);

         return $this->apiResponseFormatter->successResponse(

            'Journal Entry Saved Successfully',
            [],
            201

        );

        }catch (\Exception $e) {

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

}
