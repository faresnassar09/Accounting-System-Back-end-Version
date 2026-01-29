<?php

namespace Modules\Accounting\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use Modules\Accounting\Http\Requests\GeneralLedgerRequest;
use Modules\Accounting\Services\Reports\GeneralLedgerService;

class GeneralLedgerController extends Controller
{

    public function __construct(
        
        public GeneralLedgerService $generalLedgerService
        
        ){}

    public function generateReport(GeneralLedgerRequest $data){

        return $this->generalLedgerService->generateReport($data);
        

    }

}
