<?php

namespace Modules\Accounting\Services\CoreAccounting;
use App\Services\Api\ApiResponseFormatter;
use App\Services\Logging\LoggerService;
use Illuminate\Support\Facades\Log;
use Modules\Accounting\Repositories\Contracts\JournalEntryRepositoryInterface as JournalEntryRepository;

class OpeningBalanceService{

    public function __construct(
        
        public ApiResponseFormatter $apiResponseFormatter,
        public JournalEntryRepository $JournalEntryRepository,
        public LoggerService $loggerService, 
        
        ){}

    public function store($data){

        try{

         $this->JournalEntryRepository->storeOpeningBalanceEntry($data);

         return $this->apiResponseFormatter->successResponse(

            'Opening Balance Saved Successfully',
           [],
           
         );

        }catch(\Exception $e){
             
         $this->loggerService->failedLogger(

            'Failed To Store Opening Balance Entry',
            [],
            $e->getMessage(),
         );

         return $this->apiResponseFormatter->successResponse(

            'Failed To Save Opening Balance Entry Please Try Again Later',
            [],
            
         );
            

        }


    }

}