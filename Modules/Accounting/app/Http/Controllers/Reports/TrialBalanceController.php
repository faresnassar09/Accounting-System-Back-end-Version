<?php

namespace Modules\Accounting\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Accounting\Http\Requests\TrialBalanceRequest;
use Modules\Accounting\Services\Reports\TrialBalanceService;

class TrialBalanceController extends Controller
{

    public function __construct(

        public TrialBalanceService $trialBalanceService,
    ){}

    public function generateReport(TrialBalanceRequest $date){

        return $this->trialBalanceService->generateReport($date->endDate);


    }


}
