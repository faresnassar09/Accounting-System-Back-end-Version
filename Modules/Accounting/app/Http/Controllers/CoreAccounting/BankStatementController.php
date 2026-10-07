<?php

namespace Modules\Accounting\Http\Controllers\CoreAccounting;

use App\Http\Controllers\Controller;
use App\Services\Api\ApiResponseFormatter;
use Illuminate\Http\Request;
use Modules\Accounting\Http\Requests\StoreBankStatementRequest;
use Modules\Accounting\Http\Requests\MatchLineRequest;
use Modules\Accounting\Http\Requests\UnmatchLineRequest;
use Modules\Accounting\Http\Requests\UnreconciledLedgerLinesRequest;
use Modules\Accounting\Services\CoreAccounting\BankStatementService;

class BankStatementController extends Controller
{
    public function __construct(
        protected BankStatementService $bankStatementService,
        protected ApiResponseFormatter $apiResponseFormatter
    ) {}

    public function index(Request $request)
    {
        try {
            $accountId = $request->query('account_id');
            $status = $request->query('status');

            $statements = $this->bankStatementService->getStatements($accountId, $status);

            return $this->apiResponseFormatter->successResponse(
                'Bank statements retrieved successfully',
                $statements
            );
        } catch (\Exception $e) {
            return $this->apiResponseFormatter->failedResponse(
                'Failed retrieving bank statements: ' . $e->getMessage(),
                [],
                500
            );
        }
    }

    public function store(StoreBankStatementRequest $request)
    {
        try {
            $data = $request->validated();

            $statement = $this->bankStatementService->importStatement($data);

            return $this->apiResponseFormatter->successResponse(
                'Bank statement imported successfully',
                $statement->load(['account', 'lines']),
                201
            );
        } catch (\Exception $e) {
            return $this->apiResponseFormatter->failedResponse(
                'Failed importing bank statement: ' . $e->getMessage(),
                [],
                422
            );
        }
    }

    public function show(int $id)
    {
        try {
            $statement = $this->bankStatementService->getStatementById($id);

            return $this->apiResponseFormatter->successResponse(
                'Bank statement retrieved successfully',
                $statement
            );
        } catch (\Exception $e) {
            return $this->apiResponseFormatter->failedResponse(
                'Bank statement not found: ' . $e->getMessage(),
                [],
                404
            );
        }
    }

    public function autoMatch(int $id, Request $request)
    {
        try {
            $windowDays = (int) ($request->input('date_window_days') ?? 5);

            $result = $this->bankStatementService->autoMatch($id, $windowDays);

            return $this->apiResponseFormatter->successResponse(
                "Auto-matching completed. {$result['matched_count']} lines matched.",
                $result
            );
        } catch (\Exception $e) {
            return $this->apiResponseFormatter->failedResponse(
                'Auto-matching failed: ' . $e->getMessage(),
                [],
                422
            );
        }
    }

    public function matchLine(int $id, MatchLineRequest $request)
    {
        try {
            $validated = $request->validated();
            $stmtLine = $this->bankStatementService->matchLine($validated['statement_line_id'], $validated['journal_entry_line_id']);

            return $this->apiResponseFormatter->successResponse(
                'Lines matched successfully',
                $stmtLine
            );
        } catch (\Exception $e) {
            return $this->apiResponseFormatter->failedResponse(
                'Failed matching lines: ' . $e->getMessage(),
                [],
                422
            );
        }
    }

    public function unmatchLine(int $id, UnmatchLineRequest $request)
    {
        try {
            $validated = $request->validated();
            $stmtLine = $this->bankStatementService->unmatchLine($validated['statement_line_id']);

            return $this->apiResponseFormatter->successResponse(
                'Line unmatched successfully',
                $stmtLine
            );
        } catch (\Exception $e) {
            return $this->apiResponseFormatter->failedResponse(
                'Failed unmatching line: ' . $e->getMessage(),
                [],
                422
            );
        }
    }

    public function reconcile(int $id)
    {
        try {
            $statement = $this->bankStatementService->reconcile($id);

            return $this->apiResponseFormatter->successResponse(
                'Bank statement reconciled and closed successfully',
                $statement
            );
        } catch (\Exception $e) {
            return $this->apiResponseFormatter->failedResponse(
                'Failed reconciling statement: ' . $e->getMessage(),
                [],
                422
            );
        }
    }

    public function unreconciledLedgerLines(UnreconciledLedgerLinesRequest $request)
    {
        try {
            $validated = $request->validated();
            $accountId = (int) $validated['account_id'];
            $asOfDate = $validated['as_of_date'] ?? null;

            $lines = $this->bankStatementService->getUnreconciledLedgerLines($accountId, $asOfDate);

            return $this->apiResponseFormatter->successResponse(
                'Unreconciled ledger lines retrieved successfully',
                $lines
            );
        } catch (\Exception $e) {
            return $this->apiResponseFormatter->failedResponse(
                'Failed retrieving unreconciled ledger lines: ' . $e->getMessage(),
                [],
                500
            );
        }
    }
}
