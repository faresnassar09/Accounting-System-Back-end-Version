<?php

namespace Modules\Accounting\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use App\Services\Api\ApiResponseFormatter;
use App\Services\Logging\LoggerService;
use Modules\Accounting\Exports\TrialBalanceExport;
use Modules\Accounting\Http\Requests\TrialBalanceRequest;
use Modules\Accounting\Services\Reports\ReportExportService;
use Modules\Accounting\Services\Reports\TrialBalanceService;
use Modules\Accounting\Transformers\TrialBalanceResource;

class TrialBalanceController extends Controller
{
    public function __construct(
        public TrialBalanceService $trialBalanceService,
        public LoggerService $loggerService,
        public ApiResponseFormatter $apiResponseFormatter,
        public ReportExportService $reportExportService,
    ) {}

    /**
     * Generate trial balance report 
     *
     * @group trial balance report
     */
    public function generateReport(TrialBalanceRequest $request)
    {
        try {
            $endDate = $request->input('endDate') ?? $request->input('end_date') ?? now()->format('Y-m-d');
            $branchId = $request->input('branch_id') ?? $request->input('branchId');
            $branchId = $branchId ? (int) $branchId : null;

            $export = strtolower((string) $request->input('export'));
            $isExport = ! empty($export) || $request->boolean('send_email') || $request->filled('email') || $request->has('attachments') || $request->has('attach');

            // If an export or email is requested, enforce backend queuing (no direct download, no frontend queue override)
            if ($isExport) {
                $recipientEmail = $request->input('email') ?: auth()->user()?->email;

                if (! $recipientEmail) {
                    return $this->apiResponseFormatter->failedResponse(
                        'A valid recipient email is required to receive the exported report',
                        ['email' => ['Please provide an email address or ensure your user profile has an email.']],
                        422
                    );
                }

                $formats = $this->reportExportService->resolveAttachments($request, $export);

                $this->reportExportService->dispatchReportJob(
                    reportType: 'trial-balance',
                    parameters: ['endDate' => $endDate, 'branch_id' => $branchId],
                    recipientEmail: $recipientEmail,
                    formats: $formats
                );

                $formatLabel = strtoupper(implode(' & ', $formats));
                return $this->apiResponseFormatter->successResponse(
                    "Trial Balance report generation has been queued. The [{$formatLabel}] report will be sent to {$recipientEmail} shortly.",
                    null
                );
            }

            // Otherwise, render standard JSON for frontend UI display
            $reportData = $this->trialBalanceService->generateReport($endDate, $branchId);

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
