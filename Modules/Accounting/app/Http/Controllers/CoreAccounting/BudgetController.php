<?php

namespace Modules\Accounting\Http\Controllers\CoreAccounting;

use App\Http\Controllers\Controller;
use App\Services\Api\ApiResponseFormatter;
use Illuminate\Http\Request;
use Modules\Accounting\Http\Requests\StoreBudgetRequest;
use Modules\Accounting\Http\Requests\UpdateBudgetRequest;
use Modules\Accounting\Services\CoreAccounting\BudgetService;

class BudgetController extends Controller
{
    public function __construct(
        protected BudgetService $budgetService,
        protected ApiResponseFormatter $apiResponseFormatter
    ) {}

    public function index(Request $request)
    {
        try {
            $year = $request->query('fiscal_year') ?? $request->query('fiscalYear') ?? date('Y');
            $branchId = $request->query('branch_id') ?? $request->query('branchId');

            $budgets = $this->budgetService->getBudgets((int) $year, $branchId);

            return $this->apiResponseFormatter->successResponse(
                'Budgets retrieved successfully',
                $budgets
            );
        } catch (\Exception $e) {
            return $this->apiResponseFormatter->failedResponse(
                'Failed retrieving budgets: ' . $e->getMessage(),
                [],
                500
            );
        }
    }

    public function store(StoreBudgetRequest $request)
    {
        try {
            $data = $request->validated();
            $budget = $this->budgetService->createBudget($data);

            return $this->apiResponseFormatter->successResponse(
                'Budget created successfully',
                $budget->load(['account.accountType', 'branch']),
                201
            );
        } catch (\Exception $e) {
            return $this->apiResponseFormatter->failedResponse(
                'Failed creating budget: ' . $e->getMessage(),
                [],
                422
            );
        }
    }

    public function show(int $id)
    {
        try {
            $budget = $this->budgetService->getBudgetById($id);

            return $this->apiResponseFormatter->successResponse(
                'Budget retrieved successfully',
                $budget
            );
        } catch (\Exception $e) {
            return $this->apiResponseFormatter->failedResponse(
                'Budget not found: ' . $e->getMessage(),
                [],
                404
            );
        }
    }

    public function update(int $id, UpdateBudgetRequest $request)
    {
        try {
            $validated = $request->validated();
            $budget = $this->budgetService->updateBudget($id, $validated);

            return $this->apiResponseFormatter->successResponse(
                'Budget updated successfully',
                $budget->load(['account.accountType', 'branch'])
            );
        } catch (\Exception $e) {
            return $this->apiResponseFormatter->failedResponse(
                'Failed updating budget: ' . $e->getMessage(),
                [],
                422
            );
        }
    }

    public function destroy(int $id)
    {
        try {
            $this->budgetService->deleteBudget($id);

            return $this->apiResponseFormatter->successResponse(
                'Budget deleted successfully',
                null
            );
        } catch (\Exception $e) {
            return $this->apiResponseFormatter->failedResponse(
                'Failed deleting budget: ' . $e->getMessage(),
                [],
                422
            );
        }
    }
}
