<?php

namespace Modules\Accounting\Http\Controllers\External\Reports;

use App\Http\Controllers\Controller;
use App\Services\Api\ApiResponseFormatter;
use App\Services\Logging\LoggerService;
use Illuminate\Http\Request;
use Modules\Accounting\Models\Account;
use Modules\Accounting\Services\Reports\GeneralLedgerService;
use Modules\Accounting\Transformers\GeneralLedgerResource;

class GeneralLedgerController extends Controller
{
    public function __construct(
        public GeneralLedgerService $generalLedgerService,
        public ApiResponseFormatter $apiResponseFormatter,
        public LoggerService $loggerService,
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
            $params = [
                'accountId'  => $accountId,
                'startDate'  => $request->input('startDate') ?? $request->input('start_date'),
                'endDate'    => $request->input('endDate') ?? $request->input('end_date'),
            ];

            $reportData = $this->generalLedgerService->generateReport($params);

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
