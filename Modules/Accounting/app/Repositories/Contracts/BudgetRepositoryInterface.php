<?php

namespace Modules\Accounting\Repositories\Contracts;

use Modules\Accounting\Models\Budget;
use Illuminate\Database\Eloquent\Collection;

interface BudgetRepositoryInterface
{
    public function getBudgets(int $year, ?int $branchId): Collection;
    public function getBudgetById(int $id): Budget;
    public function createBudget(array $data): Budget;
    public function updateBudget(int $id, array $data): Budget;
    public function deleteBudget(int $id): bool;
}
