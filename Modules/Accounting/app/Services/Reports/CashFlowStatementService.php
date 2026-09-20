<?php

namespace Modules\Accounting\Services\Reports;

use Modules\Accounting\Queries\CashFlowStatementQuery;

class CashFlowStatementService
{
    public function __construct(
        public CashFlowStatementQuery $query,
    ) {}

    public function generateReport(?string $startDate = null, ?string $endDate = null, ?int $branchId = null): array
    {
        $endDate   = $endDate ?: now()->format('Y-m-d');
        $startDate = $startDate ?: get_start_of_year($endDate);

        return ($this->query)($startDate, $endDate, $branchId);
    }
}
