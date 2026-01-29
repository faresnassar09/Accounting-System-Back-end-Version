<?php

namespace Modules\Accounting\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use App\Services\Api\ApiResponseFormatter;
use App\Services\Logging\LoggerService;
use Illuminate\Http\Request;
use Modules\Accounting\Http\Requests\TrialBalanceRequest;
use Modules\Accounting\Services\Reports\TrialBalanceService;
use Modules\Accounting\Transformers\TrialBalanceResource;

class TrialBalanceController extends Controller
{

    public function __construct(

        public TrialBalanceService $trialBalanceService,
        public LoggerService $loggerService,
        public ApiResponseFormatter $apiResponseFormatter,
    ) {}

    public function generateReport(TrialBalanceRequest $date)
    {

        try {

            $reportData = $this->trialBalanceService->generateReport($date->endDate);

            return $this->apiResponseFormatter->successResponse(

                'Trial Balance Report Generated Successfully',

                new TrialBalanceResource($reportData),
                
            );
        } catch (\Exception $e) {


            $this->loggerService->failedLogger(

                'Error Occurred While Generating Trial Balance Report',
                [],
                $e->getMessage()
            );

            return $this->apiResponseFormatter->failedResponse(

                'Failed To Generate Trial Balance Report',
                [],
                500,
            );
        }
    }
}
