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

            $data = $this->incomeStatementService->generateReport($startDate, $endDate);

            $export = strtolower((string) $request->input('export'));
            $filename = 'income_statement_' . ($endDate ?? now()->format('Y-m-d'));
            $shouldEmail = $request->boolean('send_email') || $request->filled('email') || $request->has('attachments') || $request->has('attach');
            $attachments = [];
            $recipientEmail = null;

            // 1. Dispatch email with user-selected attachments (PDF, Excel, or both) if requested
            if ($shouldEmail) {
                $recipientEmail = $request->input('email') ?: auth()->user()?->email;

                if (! $recipientEmail) {
                    return $this->apiResponseFormatter->failedResponse(
                        'A valid recipient email is required to send the report',
                        ['email' => ['Please provide an email address or ensure your user profile has an email.']],
                        422
                    );
                }

                $attachments = $this->reportExportService->resolveAttachments($request, $export);
                $isQueued = $request->boolean('queue');
                $period = ($startDate && $endDate)
                    ? "Period: {$startDate} to {$endDate}"
                    : ($endDate ? "As of {$endDate}" : 'All Time');

                try {
                    $this->reportExportService->sendReportMail(
                        recipientEmail: $recipientEmail,
                        reportTitle: 'Income Statement',
                        period: $period,
                        filenameBase: $filename,
                        view: 'accounting::reports.pdf.income-statement',
                        viewData: [
                            'data'      => $data,
                            'startDate' => $startDate,
                            'endDate'   => $endDate,
                        ],
                        exportObject: new IncomeStatementExport($data, $startDate, $endDate),
                        formats: $attachments,
                        queue: $isQueued,
                    );
                } catch (\Throwable $mailError) {
                    $this->loggerService->failedLogger(
                        'Failed to send income statement report email',
                        ['recipient' => $recipientEmail, 'formats' => $attachments],
                        $mailError->getMessage()
                    );
                }
            }

            // 2. Return binary download if export format is specified
            if ($export === 'pdf') {
                return $this->reportExportService->exportPdf(
                    'accounting::reports.pdf.income-statement',
                    [
                        'data'      => $data,
                        'startDate' => $startDate,
                        'endDate'   => $endDate,
                    ],
                    $filename
                );
            }

            if ($export === 'excel') {
                return $this->reportExportService->exportExcel(
                    new IncomeStatementExport($data, $startDate, $endDate),
                    $filename
                );
            }

            // 3. Return standard JSON response
            $formatString = ! empty($attachments) ? strtoupper(implode(' & ', $attachments)) : 'PDF & EXCEL';
            $message = $shouldEmail 
                ? "Income Statement Report [{$formatString}] Generated and Emailed Successfully to {$recipientEmail}" 
                : 'Income Statement Report Generated Successfully';

            return $this->apiResponseFormatter->successResponse(
                $message,
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
