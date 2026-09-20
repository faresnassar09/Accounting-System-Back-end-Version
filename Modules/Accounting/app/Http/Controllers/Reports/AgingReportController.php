<?php

namespace Modules\Accounting\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Accounting\Services\Reports\AgingReportService;

class AgingReportController extends Controller
{
    public function __construct(
        protected AgingReportService $agingService,
    ) {}

    public function arAging(Request $request)
    {
        $asOfDate = $request->input('as_of_date') ?? $request->input('asOfDate') ?? now()->toDateString();
        $branchId = $request->input('branch_id') ?? $request->input('branchId');

        $data = $this->agingService->getAgingReport('receivable', $asOfDate, $branchId ? (int) $branchId : null);

        if ($request->input('export') === 'pdf') {
            return $this->agingService->generatePdf($data)->stream("ar-aging-{$asOfDate}.pdf");
        }

        if ($request->input('export') === 'excel') {
            return $this->agingService->generateExcel($data);
        }

        return response()->json([
            'status'  => true,
            'message' => 'Accounts Receivable aging report retrieved successfully.',
            'data'    => $data,
        ]);
    }

    public function apAging(Request $request)
    {
        $asOfDate = $request->input('as_of_date') ?? $request->input('asOfDate') ?? now()->toDateString();
        $branchId = $request->input('branch_id') ?? $request->input('branchId');

        $data = $this->agingService->getAgingReport('payable', $asOfDate, $branchId ? (int) $branchId : null);

        if ($request->input('export') === 'pdf') {
            return $this->agingService->generatePdf($data)->stream("ap-aging-{$asOfDate}.pdf");
        }

        if ($request->input('export') === 'excel') {
            return $this->agingService->generateExcel($data);
        }

        return response()->json([
            'status'  => true,
            'message' => 'Accounts Payable aging report retrieved successfully.',
            'data'    => $data,
        ]);
    }
}
