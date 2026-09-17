<?php

namespace Modules\Accounting\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use App\Services\Api\ApiResponseFormatter;
use App\Services\Logging\LoggerService;
use Modules\Accounting\Exports\BalanceSheetExport;
use Modules\Accounting\Http\Requests\BalanceSheetRequest;
use Modules\Accounting\Services\Reports\BalanceSheetService;
use Modules\Accounting\Services\Reports\ReportExportService;
use Modules\Accounting\Transformers\BalanceSheetResource;

class BalanceSheetController extends Controller
{
    public function __construct(
        public BalanceSheetService $balanceSheetService,
        public LoggerService $loggerService,
        public ApiResponseFormatter $apiResponseFormatter,
        public ReportExportService $reportExportService,
    ) {}

    public function generateReport(BalanceSheetRequest $request)
    {
        try {
            $endDate = $request->input('endDate') ?? $request->input('end_date') ?? now()->format('Y-m-d');
            $reportData = $this->balanceSheetService->generateReport($endDate);

            $export = strtolower((string) $request->input('export'));
            $filename = 'balance_sheet_' . $endDate;
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
                $period = "As of {$endDate}";

                try {
                    $this->reportExportService->sendReportMail(
                        recipientEmail: $recipientEmail,
                        reportTitle: 'Balance Sheet',
                        period: $period,
                        filenameBase: $filename,
                        view: 'accounting::reports.pdf.balance-sheet',
                        viewData: ['data' => $reportData, 'endDate' => $endDate],
                        exportObject: new BalanceSheetExport($reportData, $endDate),
                        formats: $attachments,
                        queue: true,
                    );
                } catch (\Throwable $mailError) {
                    $this->loggerService->failedLogger(
                        'Failed to send balance sheet report email',
                        ['recipient' => $recipientEmail, 'formats' => $attachments],
                        $mailError->getMessage()
                    );
                }
            }


            // 3. Return standard JSON response
            $formatString = ! empty($attachments) ? strtoupper(implode(' & ', $attachments)) : 'PDF & EXCEL';
            $message = $shouldEmail 
                ? "Balance Sheet Report [{$formatString}] Generated and Emailed Successfully to {$recipientEmail}" 
                : 'Balance Sheet Report Generated Successfully';

            return $this->apiResponseFormatter->successResponse(
                $message,
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
                500,
            );
        }    
    }
}
