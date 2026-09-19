<?php

namespace Modules\Accounting\Http\Controllers\External\Reports;

use App\Http\Controllers\Controller;
use App\Services\Api\ApiResponseFormatter;
use App\Services\Logging\LoggerService;
use Illuminate\Http\Request;
use Modules\Accounting\Exports\GeneralLedgerExport;
use Modules\Accounting\Models\Account;
use Modules\Accounting\Services\Reports\GeneralLedgerService;
use Modules\Accounting\Services\Reports\ReportExportService;
use Modules\Accounting\Transformers\GeneralLedgerResource;

class GeneralLedgerController extends Controller
{
    public function __construct(
        public GeneralLedgerService $generalLedgerService,
        public ApiResponseFormatter $apiResponseFormatter,
        public LoggerService $loggerService,
        public ReportExportService $reportExportService,
    ) {}

    /**
     * Generate General Ledger Report for External Services
     */
    public function generateReport(Request $request)
    {
        $accountId = $request->input('accountId') ?? $request->input('account_id');
        $accountNumber = $request->input('accountNumber') ?? $request->input('account_number');

        if (! $accountId && $accountNumber) {
            $accountId = Account::where('number', $accountNumber)->value('id');
        }

        if (! $accountId) {
            return $this->apiResponseFormatter->failedResponse(
                'Valid accountId or account_number is required',
                ['account' => ['The specified account could not be found.']],
                422
            );
        }

        try {
            $startDate = $request->input('startDate') ?? $request->input('start_date');
            $endDate   = $request->input('endDate') ?? $request->input('end_date');

            $params = [
                'accountId'  => $accountId,
                'startDate'  => $startDate,
                'endDate'    => $endDate,
                'branch_id'  => $request->input('branch_id') ?? $request->input('branchId'),
            ];

            $reportData = $this->generalLedgerService->generateReport($params);

            $export = strtolower((string) $request->input('export'));
            $accountNum = $reportData['account_info']['number'] ?? 'account';
            $filename = 'general_ledger_' . $accountNum . '_' . ($endDate ?? now()->format('Y-m-d'));

            if ($export === 'pdf') {
                return $this->reportExportService->exportPdf(
                    'accounting::reports.pdf.general-ledger',
                    [
                        'data'      => $reportData,
                        'startDate' => $startDate,
                        'endDate'   => $endDate,
                    ],
                    $filename
                );
            }

            if ($export === 'excel') {
                return $this->reportExportService->exportExcel(
                    new GeneralLedgerExport($reportData, $startDate, $endDate),
                    $filename
                );
            }

            return $this->apiResponseFormatter->successResponse(
                'General Ledger Report Generated Successfully',
                new GeneralLedgerResource($reportData),
            );
        } catch (\Exception $e) {
            $this->loggerService->failedLogger(
                'External API: Error Generating General Ledger Report',
                [],
                $e->getMessage()
            );

            return $this->apiResponseFormatter->failedResponse(
                'Failed To Generate General Ledger Report',
                [],
                500
            );
        }
    }
}
