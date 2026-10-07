<?php

namespace Modules\Accounting\Repositories\Eloquent;

use Illuminate\Database\Eloquent\Collection;
use Modules\Accounting\Models\Budget;
use Modules\Accounting\Repositories\Contracts\BudgetRepositoryInterface;

class BudgetRepository implements BudgetRepositoryInterface
{
    public function getBudgets(int $year, ?int $branchId): Collection
    {
        return Budget::query()
            ->with(['account.accountType', 'branch'])
            ->where('fiscal_year', $year)
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->orderBy('id', 'desc')
            ->get();
    }

    public function getBudgetById(int $id): Budget
    {
        return Budget::with(['account.accountType', 'branch'])->findOrFail($id);
    }

    public function createBudget(array $data): Budget
    {
        return Budget::create($data);
    }

    public function updateBudget(int $id, array $data): Budget
    {
        $budget = Budget::findOrFail($id);
        $budget->update($data);
        return $budget;
    }

    public function deleteBudget(int $id): bool
    {
        $budget = Budget::findOrFail($id);
        return $budget->delete();
    }
}
