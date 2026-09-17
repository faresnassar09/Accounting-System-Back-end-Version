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
            $reportData = $this->trialBalanceService->generateReport($endDate);

            $export = strtolower((string) $request->input('export'));
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

                try {
                    $this->reportExportService->sendReportMail(
                        recipientEmail: $recipientEmail,
                        reportTitle: 'Trial Balance',
                        period: "As of {$endDate}",
                        filenameBase: 'trial_balance_' . $endDate,
                        view: 'accounting::reports.pdf.trial-balance',
                        viewData: ['data' => $reportData, 'endDate' => $endDate],
                        exportObject: new TrialBalanceExport($reportData, $endDate),
                        formats: $attachments,
                        queue: $isQueued,
                    );
                } catch (\Throwable $mailError) {
                    $this->loggerService->failedLogger(
                        'Failed to send trial balance report email',
                        ['recipient' => $recipientEmail, 'formats' => $attachments],
                        $mailError->getMessage()
                    );
                }
            }

            // 2. Return binary download if export format is specified
            if ($export === 'pdf') {
                return $this->reportExportService->exportPdf(
                    'accounting::reports.pdf.trial-balance',
                    ['data' => $reportData, 'endDate' => $endDate],
                    'trial_balance_' . $endDate
                );
            }

            if ($export === 'excel') {
                return $this->reportExportService->exportExcel(
                    new TrialBalanceExport($reportData, $endDate),
                    'trial_balance_' . $endDate
                );
            }

            // 3. Return standard JSON response
            $formatString = ! empty($attachments) ? strtoupper(implode(' & ', $attachments)) : 'PDF & EXCEL';
            $message = $shouldEmail 
                ? "Trial Balance Report [{$formatString}] Generated and Emailed Successfully to {$recipientEmail}" 
                : 'Trial Balance Report Generated Successfully';

            return $this->apiResponseFormatter->successResponse(
                $message,
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
