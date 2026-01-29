<?php

namespace Modules\Accounting\Http\Controllers\CoreAccounting;

use App\Http\Controllers\Controller;
use App\Services\Api\ApiResponseFormatter;
use App\Services\Logging\LoggerService;
use Modules\Accounting\Services\CoreAccounting\OpeningBalanceService;
use Modules\Accounting\Http\Requests\OpeningBalanceRequest;

class OpeningBalanceController extends Controller
{

    public function __construct(

        public OpeningBalanceService $openingBalanceService,
        public LoggerService $loggerService,
        public ApiResponseFormatter $apiResponseFormatter,

    ){} 

    public function store(OpeningBalanceRequest $data) {


        try{

            $this->openingBalanceService->store($data);

            return $this->apiResponseFormatter->successResponse(

                'Opening Balance Saved Successfully',
                [],

            );

        }        catch (\Exception $e) {

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
