<?php

namespace Modules\Accounting\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use App\Services\Api\ApiResponseFormatter;
use Illuminate\Http\Request;
use Modules\Accounting\Services\Reports\BudgetVsActualService;

class BudgetVsActualController extends Controller
{
    public function __construct(
        protected BudgetVsActualService $budgetVsActualService,
        protected ApiResponseFormatter $apiResponseFormatter
    ) {}

    public function generateReport(Request $request)
    {
        try {
            $fiscalYear = (int) ($request->query('fiscal_year') ?? $request->query('fiscalYear') ?? date('Y'));
            $branchId = $request->query('branch_id') ?? $request->query('branchId');
            $branchId = $branchId ? (int) $branchId : null;

            $report = $this->budgetVsActualService->generateReport($fiscalYear, $branchId);

            return $this->apiResponseFormatter->successResponse(
                'Budget vs Actual report generated successfully',
                $report
            );
        } catch (\Exception $e) {
            return $this->apiResponseFormatter->failedResponse(
                'Failed generating Budget vs Actual report: ' . $e->getMessage(),
                [],
                500
            );
        }
    }
}
