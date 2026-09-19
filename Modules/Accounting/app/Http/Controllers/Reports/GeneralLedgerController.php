<?php

namespace Modules\Accounting\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use App\Services\Api\ApiResponseFormatter;
use App\Services\Logging\LoggerService;
use Modules\Accounting\Exports\GeneralLedgerExport;
use Modules\Accounting\Http\Requests\GeneralLedgerRequest;
use Modules\Accounting\Services\Reports\GeneralLedgerService;
use Modules\Accounting\Services\Reports\ReportExportService;
use Modules\Accounting\Transformers\GeneralLedgerResource;

class GeneralLedgerController extends Controller
{
    public function __construct(
        public GeneralLedgerService $generalLedgerService,
        public LoggerService $loggerService,
        public ApiResponseFormatter $apiResponseFormatter,
        public ReportExportService $reportExportService,
    ) {}

    /**
     * Generate general ledger report 
     *
     * @group general ledger report
     */
    public function generateReport(GeneralLedgerRequest $request)
    {
        try {
            $validated = $request->validated();
            $params = [
                'accountId' => $validated['accountId'] ?? $validated['account_id'] ?? null,
                'startDate' => $validated['startDate'] ?? $validated['start_date'] ?? null,
                'endDate'   => $validated['endDate'] ?? $validated['end_date'] ?? null,
                'branch_id' => $validated['branch_id'] ?? $validated['branchId'] ?? null,
            ];

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
                    reportType: 'general-ledger',
                    parameters: $params,
                    recipientEmail: $recipientEmail,
                    formats: $formats
                );

                $formatLabel = strtoupper(implode(' & ', $formats));
                return $this->apiResponseFormatter->successResponse(
                    "General Ledger report generation has been queued. The [{$formatLabel}] report will be sent to {$recipientEmail} shortly.",
                    null
                );
            }

            // Otherwise, render standard JSON for frontend UI display
            $reportData = $this->generalLedgerService->generateReport($params);

            return $this->apiResponseFormatter->successResponse(
                'General Ledger Report Generated Successfully',
                new GeneralLedgerResource($reportData),
            );

        } catch (\Exception $e) {
            $this->loggerService->failedLogger(
                'Error Occurred While Generating General Ledger Report',
                [],
                $e->getMessage()
            );

            return $this->apiResponseFormatter->failedResponse(
                'Failed To Generate General Ledger Report',
                [],
                500,
            );
        }
    }
}
