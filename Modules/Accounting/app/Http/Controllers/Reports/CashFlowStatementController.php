<?php

namespace Modules\Accounting\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use App\Services\Api\ApiResponseFormatter;
use App\Services\Logging\LoggerService;
use Modules\Accounting\Http\Requests\CashFlowStatementRequest;
use Modules\Accounting\Services\Reports\CashFlowStatementService;
use Modules\Accounting\Services\Reports\ReportExportService;

class CashFlowStatementController extends Controller
{
    public function __construct(
        public CashFlowStatementService $cashFlowService,
        public LoggerService $loggerService,
        public ApiResponseFormatter $apiResponseFormatter,
        public ReportExportService $reportExportService,
    ) {}

    public function generateReport(CashFlowStatementRequest $request)
    {
        try {
            $endDate   = $request->input('endDate') ?? $request->input('end_date') ?? now()->format('Y-m-d');
            $startDate = $request->input('startDate') ?? $request->input('start_date') ?? get_start_of_year($endDate);

            $branchId = $request->input('branch_id') ?? $request->input('branchId');
            $branchId = $branchId ? (int) $branchId : null;

            $export = strtolower((string) $request->input('export'));
            $isExport = ! empty($export) || $request->boolean('send_email') || $request->filled('email') || $request->has('attachments') || $request->has('attach');

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
                    reportType: 'cash-flow',
                    parameters: [
                        'startDate' => $startDate,
                        'endDate'   => $endDate,
                        'branch_id' => $branchId,
                    ],
                    recipientEmail: $recipientEmail,
                    formats: $formats
                );

                $formatLabel = strtoupper(implode(' & ', $formats));
                return $this->apiResponseFormatter->successResponse(
                    "Statement of Cash Flows report generation has been queued. The [{$formatLabel}] report will be sent to {$recipientEmail} shortly.",
                    null
                );
            }

            $report = $this->cashFlowService->generateReport($startDate, $endDate, $branchId);

            $this->loggerService->successLogger(
                "Statement of Cash Flows successfully generated for period {$startDate} to {$endDate}",
                ['period' => "{$startDate} - {$endDate}"]
            );

            return $this->apiResponseFormatter->successResponse(
                'Statement of Cash Flows generated successfully',
                $report
            );
        } catch (\Exception $e) {
            $this->loggerService->errorLogger('Error generating Statement of Cash Flows: ' . $e->getMessage());

            return $this->apiResponseFormatter->failedResponse(
                'Failed to generate Statement of Cash Flows',
                ['error' => $e->getMessage()],
                500
            );
        }
    }
}
