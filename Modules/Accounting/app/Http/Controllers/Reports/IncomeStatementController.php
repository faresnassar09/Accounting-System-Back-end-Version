<?php

namespace Modules\Accounting\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Accounting\Http\Requests\IncomeStatementRequest;
use Modules\Accounting\Services\Reports\IncomeStatementService;

class IncomeStatementController extends Controller
{

    public function __construct(public IncomeStatementService $incomeStatementService){}

    public function generateReport(IncomeStatementRequest $data){

    return $this->incomeStatementService->generateReport($data->startDate,$data->endDate);
    }
}
