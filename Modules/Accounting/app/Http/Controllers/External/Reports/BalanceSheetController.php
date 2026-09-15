<?php

namespace Modules\Accounting\Http\Controllers\External\Reports;

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
        public ApiResponseFormatter $apiResponseFormatter,
        public LoggerService $loggerService,
    ) {}

    /**
     * Generate Balance Sheet Report for External Services
     */
    public function generateReport(BalanceSheetRequest $request)
    {
        try {
            $endDate = $request->input('endDate') ?? $request->input('end_date');
            $reportData = $this->balanceSheetService->generateReport($endDate);

            return $this->apiResponseFormatter->successResponse(
                'Balance Sheet Report Generated Successfully',
                BalanceSheetResource::collection($reportData),
            );
        } catch (\Exception $e) {
            $this->loggerService->failedLogger(
                'External API: Error Generating Balance Sheet Report',
                [],
                $e->getMessage()
            );

            return $this->apiResponseFormatter->failedResponse(
                'Failed To Generate Balance Sheet Report',
                [],
                500
            );
        }
    }
}
