<?php

namespace Modules\Accounting\Http\Controllers\External\Reports;

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
        public ApiResponseFormatter $apiResponseFormatter,
        public LoggerService $loggerService,
        public ReportExportService $reportExportService,
    ) {}

    /**
     * Generate Balance Sheet Report for External Services
     */
    public function generateReport(BalanceSheetRequest $request)
    {
        try {
            $endDate = $request->input('endDate') ?? $request->input('end_date') ?? now()->format('Y-m-d');
            $branchId = $request->input('branch_id') ?? $request->input('branchId');
            $branchId = $branchId ? (int) $branchId : null;

            $reportData = $this->balanceSheetService->generateReport($endDate, $branchId);

            $export = strtolower((string) $request->input('export'));
            $filename = 'balance_sheet_' . $endDate;

            if ($export === 'pdf') {
                return $this->reportExportService->exportPdf(
                    'accounting::reports.pdf.balance-sheet',
                    ['data' => $reportData, 'endDate' => $endDate],
                    $filename
                );
            }

            if ($export === 'excel') {
                return $this->reportExportService->exportExcel(
                    new BalanceSheetExport($reportData, $endDate),
                    $filename
                );
            }

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
