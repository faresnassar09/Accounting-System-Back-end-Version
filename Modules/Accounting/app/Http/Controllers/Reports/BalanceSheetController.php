<?php

namespace Modules\Accounting\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use App\Services\Api\ApiResponseFormatter;
use App\Services\Logging\LoggerService;
use Modules\Accounting\Http\Requests\BalanceSheetRequest;
use Modules\Accounting\Services\Reports\BalanceSheetService;
use Modules\Accounting\Transformers\BalanceSheetResource;

class BalanceSheetController extends Controller
{

    public function __construct(

        public BalanceSheetService $balanceSheetService,
        public LoggerService $loggerService,
        public ApiResponseFormatter $apiResponseFormatter,

    ) {}


    public function generateReport(BalanceSheetRequest $data)
    {

        try {

           $reportData = $this->balanceSheetService->generateReport($data->endDate);
           
            return $this->apiResponseFormatter->successResponse(

                'Balance Sheet Report Generated Successfully',
                BalanceSheetResource::collection($reportData),

            );

        } catch (\Exception $e) {


            $this->loggerService->failedLogger(

                'Error Occurred While Generating Balance Sheet',
                [],
                $e->getMessage()
            );

            return $this->apiResponseFormatter->failedResponse(

                'Failed To Generate Balance Sheet Report',
                [],
            );
        }    
    }
}
