<?php

namespace Modules\Accounting\Services\CoreAccounting;

use Modules\Accounting\Models\Budget;
use Modules\Accounting\Repositories\Contracts\BudgetRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class BudgetService
{
    public function __construct(
        protected BudgetRepositoryInterface $repository
    ) {}

    public function getBudgets(int $year, ?int $branchId): Collection
    {
        return $this->repository->getBudgets($year, $branchId);
    }

    public function getBudgetById(int $id): Budget
    {
        return $this->repository->getBudgetById($id);
    }

    public function createBudget(array $data): Budget
    {
        if (empty($data['created_by'])) {
            $data['created_by'] = auth()->id();
        }
        return $this->repository->createBudget($data);
    }

    public function updateBudget(int $id, array $data): Budget
    {
        return $this->repository->updateBudget($id, $data);
    }

    public function deleteBudget(int $id): bool
    {
        return $this->repository->deleteBudget($id);
    }
}
