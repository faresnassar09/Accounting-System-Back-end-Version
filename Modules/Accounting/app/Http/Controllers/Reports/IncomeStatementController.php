<?php

namespace Modules\Accounting\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use App\Services\Api\ApiResponseFormatter;
use App\Services\Logging\LoggerService;
use Modules\Accounting\Exports\IncomeStatementExport;
use Modules\Accounting\Http\Requests\IncomeStatementRequest;
use Modules\Accounting\Services\Reports\IncomeStatementService;
use Modules\Accounting\Services\Reports\ReportExportService;
use Modules\Accounting\Transformers\IncomeStatementResource;

class IncomeStatementController extends Controller
{
    public function __construct(
        public IncomeStatementService $incomeStatementService,
        public ApiResponseFormatter $apiResponseFormatter,
        public LoggerService $loggerService,
        public ReportExportService $reportExportService,
    ) {}

    /**
     * Generate income statement report 
     *
     * @group income statement report
     */
    public function generateReport(IncomeStatementRequest $request)
    {
        try {
            $startDate = $request->input('startDate') ?? $request->input('start_date');
            $endDate   = $request->input('endDate') ?? $request->input('end_date');

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
                    reportType: 'income-statement',
                    parameters: [
                        'startDate' => $startDate,
                        'endDate'   => $endDate,
                    ],
                    recipientEmail: $recipientEmail,
                    formats: $formats
                );

                $formatLabel = strtoupper(implode(' & ', $formats));
                return $this->apiResponseFormatter->successResponse(
                    "Income Statement report generation has been queued. The [{$formatLabel}] report will be sent to {$recipientEmail} shortly.",
                    null
                );
            }

            // Otherwise, render standard JSON for frontend UI display
            $data = $this->incomeStatementService->generateReport($startDate, $endDate);

            return $this->apiResponseFormatter->successResponse(
                'Income Statement Report Generated Successfully',
                new IncomeStatementResource($data),
            );
        } catch (\Exception $e) {
            $this->loggerService->failedLogger(
                'Error Occurred While Generating Income Statement Report',
                [],
                $e->getMessage()
            );

            return $this->apiResponseFormatter->failedResponse(
                'Failed To Generate Income Statement Report',
                [],
                500,
            );
        }
    }
}
