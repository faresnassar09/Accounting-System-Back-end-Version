<?php

namespace Modules\Accounting\Http\Controllers\CoreAccounting;

use App\Http\Controllers\Controller;
use App\Services\Api\ApiResponseFormatter;
use App\Services\Logging\LoggerService;
use Modules\Accounting\Services\CoreAccounting\AccountingCharService;
use Modules\Accounting\Transformers\AccountListResource;
use Modules\Accounting\Transformers\ClosingAccountsListResource;

class AccountingChartController extends Controller
{

  public function __construct(
    public AccountingCharService $accountingCharService,
    public ApiResponseFormatter $apiResponseFormatter,
    public LoggerService $loggerService,

  ) {}

  public function getAccountingChart()
  {

    try {

      $data =  $this->accountingCharService->viewChartTree();

      return $this->apiResponseFormatter->successResponse(

        'Chart Tree Reterived Successfully',
        $data,


      );
    } catch (\Exception $e) {

      $this->loggerService->failedLogger(

        'Error Occurred While Retrieving Chart Accounting',
        [],
        $e->getMessage()

      );

      return $this->apiResponseFormatter->failedResponse(

        'Error Occurred While Retrieving Chart Accounting',
        [],

      );
    }
  }


  public function getAccounts()
  {


    try {

      $accounts = $this->accountingCharService->getAccounts();

      return $this->apiResponseFormatter->successResponse(

        'Accounts Retrieved Successfully',
        AccountListResource::collection($accounts),

      );
    } catch (\Exception $e) {

      $this->loggerService->failedLogger(
        'Error Occurred While Retrieving Accounts List',
        [],
        $e->getMessage()

      );

      return $this->apiResponseFormatter->failedResponse(

        'Error Occurred While Retrieving Accounts List',
        [],

      );
    }
  }

  public function getClosingAccounts(){

    try {
      
      $data = $this->accountingCharService->getClosingAccounts();

      return $this->apiResponseFormatter->successResponse(

        'Closing Accounts Retrieved Successfully',
         ClosingAccountsListResource::collection($data),

      );

    } catch (\Exception $e) {


      $this->loggerService->failedLogger(
        'Error Occurred While Retrieving Closing Accounts List',
        [],
        $e->getMessage()

      );

      return $this->apiResponseFormatter->failedResponse(

        'Error Occurred While Retrieving Closing Accounts List',
        [],

      );

    }

  }
}
