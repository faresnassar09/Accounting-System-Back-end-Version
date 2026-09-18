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
                    reportType: 'balance-sheet',
                    parameters: ['endDate' => $endDate],
                    recipientEmail: $recipientEmail,
                    formats: $formats
                );

                $formatLabel = strtoupper(implode(' & ', $formats));
                return $this->apiResponseFormatter->successResponse(
                    "Balance Sheet report generation has been queued. The [{$formatLabel}] report will be sent to {$recipientEmail} shortly.",
                    null
                );
            }

            // Otherwise, render standard JSON for frontend UI display
            $reportData = $this->balanceSheetService->generateReport($endDate);

            return $this->apiResponseFormatter->successResponse(
                'Balance Sheet Report Generated Successfully',
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
