<?php

namespace Modules\Accounting\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Accounting\Http\Requests\BalanceSheetRequest;
use Modules\Accounting\Repositories\Contracts\BalanceSheetRepositoryInterface;
use Modules\Accounting\Services\Reports\BalanceSheetService;

class BalanceSheetController extends Controller
{

    public function __construct(

        public BalanceSheetService $balanceSheetService,

    ) {}


    public function generateReport(BalanceSheetRequest $data)
    {

        return $this->balanceSheetService->generateReport($data->endDate);
    }
}
