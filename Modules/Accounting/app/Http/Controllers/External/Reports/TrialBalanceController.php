<?php

namespace Modules\Accounting\Http\Controllers\External\Reports;

use App\Http\Controllers\Controller;
use App\Services\Api\ApiResponseFormatter;
use App\Services\Logging\LoggerService;
use Modules\Accounting\Http\Requests\TrialBalanceRequest;
use Modules\Accounting\Services\Reports\TrialBalanceService;
use Modules\Accounting\Transformers\TrialBalanceResource;

class TrialBalanceController extends Controller
{
    public function __construct(
        public TrialBalanceService $trialBalanceService,
        public ApiResponseFormatter $apiResponseFormatter,
        public LoggerService $loggerService,
    ) {}

    /**
     * Generate Trial Balance Report for External Services
     */
    public function generateReport(TrialBalanceRequest $request)
    {
        try {
            $endDate = $request->input('endDate') ?? $request->input('end_date') ?? now()->format('Y-m-d');
            $reportData = $this->trialBalanceService->generateReport($endDate);

            return $this->apiResponseFormatter->successResponse(
                'Trial Balance Report Generated Successfully',
                new TrialBalanceResource($reportData),
            );
        } catch (\Exception $e) {
            $this->loggerService->failedLogger(
                'External API: Error Generating Trial Balance Report',
                [],
                $e->getMessage()
            );

            return $this->apiResponseFormatter->failedResponse(
                'Failed To Generate Trial Balance Report',
                [],
                500
            );
        }
    }
}
