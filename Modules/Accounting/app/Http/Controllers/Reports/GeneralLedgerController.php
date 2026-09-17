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
            ];

            $reportData = $this->generalLedgerService->generateReport($params);

            $export = strtolower((string) $request->input('export'));
            $accountNumber = $reportData['account_info']['number'] ?? 'account';
            $accountName = $reportData['account_info']['name'] ?? 'Account';
            $filename = 'general_ledger_' . $accountNumber . '_' . ($params['endDate'] ?? now()->format('Y-m-d'));
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
                $period = 'Period: ' . ($params['startDate'] ?? 'Beginning') . ' to ' . ($params['endDate'] ?? now()->format('Y-m-d'));

                try {
                    $this->reportExportService->sendReportMail(
                        recipientEmail: $recipientEmail,
                        reportTitle: "General Ledger - {$accountName} ({$accountNumber})",
                        period: $period,
                        filenameBase: $filename,
                        view: 'accounting::reports.pdf.general-ledger',
                        viewData: [
                            'data'      => $reportData,
                            'startDate' => $params['startDate'],
                            'endDate'   => $params['endDate'],
                        ],
                        exportObject: new GeneralLedgerExport($reportData, $params['startDate'], $params['endDate']),
                        formats: $attachments,
                        queue: true,
                    );
                } catch (\Throwable $mailError) {
                    $this->loggerService->failedLogger(
                        'Failed to send general ledger report email',
                        ['recipient' => $recipientEmail, 'formats' => $attachments],
                        $mailError->getMessage()
                    );
                }
            }

            // 3. Return standard JSON response
            $formatString = ! empty($attachments) ? strtoupper(implode(' & ', $attachments)) : 'PDF & EXCEL';
            $message = $shouldEmail 
                ? "General Ledger Report [{$formatString}] Generated and Emailed Successfully to {$recipientEmail}" 
                : 'General Ledger Report Generated Successfully';

            return $this->apiResponseFormatter->successResponse(
                $message,
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
