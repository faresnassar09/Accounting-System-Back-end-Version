<?php

namespace Modules\Accounting\Http\Controllers\External\Reports;

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
     * Generate Income Statement Report for External Services
     */
    public function generateReport(IncomeStatementRequest $request)
    {
        try {
            $startDate = $request->input('startDate') ?? $request->input('start_date');
            $endDate   = $request->input('endDate') ?? $request->input('end_date');

            $data = $this->incomeStatementService->generateReport($startDate, $endDate);

            $export = strtolower((string) $request->input('export'));
            $filename = 'income_statement_' . ($endDate ?? now()->format('Y-m-d'));

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

            return $this->apiResponseFormatter->successResponse(
                'Income Statement Report Generated Successfully',
                new IncomeStatementResource($data),
            );
        } catch (\Exception $e) {
            $this->loggerService->failedLogger(
                'External API: Error Generating Income Statement Report',
                [],
                $e->getMessage()
            );

            return $this->apiResponseFormatter->failedResponse(
                'Failed To Generate Income Statement Report',
                [],
                500
            );
        }
    }
}
