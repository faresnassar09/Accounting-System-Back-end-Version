<?php

namespace Modules\Accounting\Http\Controllers\CoreAccounting;

use App\Http\Controllers\Controller;
use Modules\Accounting\Services\CoreAccounting\AccountingCharService;
class AccountingChartController extends Controller
{

    public function __construct(public AccountingCharService $accountingCharService){}
    
    public function getAccountingChart(){

       return $this->accountingCharService->viewChartTree();


    }

    
    public function getAccounts()
    {

      return  $this->accountingCharService->getAccounts();

    }
}
