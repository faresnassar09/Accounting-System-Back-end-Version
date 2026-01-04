<?php

namespace Modules\Accounting\Repositories\Contracts;

interface IncomeStatementRepositoryInterface{ 

    public function getRevenueExpenseAccounts($startDate,$endDate);


}