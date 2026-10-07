<?php

namespace Modules\Accounting\Http\Controllers\CoreAccounting;

use App\Http\Controllers\Controller;
use App\Services\Api\ApiResponseFormatter;
use App\Services\Logging\LoggerService;
use Modules\Accounting\Http\Requests\StoreAccountRequest;
use Modules\Accounting\Http\Requests\UpdateAccountRequest;
use Modules\Accounting\Models\Account;
use Modules\Accounting\Models\AccountType;
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


  /**
   * 
   * retrieve the chart accounting's accounts  
   *
   * @group chart accounting
   */
  

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

  /**
   * 
   * retrieve all accounts  
   *
   * @group chart accounting
   */
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


  /**
   * 
   * retrieve the closing accounts  
   *
   * @group chart accounting
   */

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

  public function getAccountTypes()
  {
    try {
      $types = AccountType::select('id', 'type', 'account_group')
        ->orderBy('account_group')
        ->orderBy('type')
        ->get()
        ->map(function ($t) {
          return [
            'id' => $t->id,
            'type' => $t->type,
            'name' => ucwords(str_replace('_', ' ', $t->type)),
            'account_group' => $t->account_group,
          ];
        });

      return $this->apiResponseFormatter->successResponse(
        'Account Types Retrieved Successfully',
        $types
      );
    } catch (\Exception $e) {
      return $this->apiResponseFormatter->failedResponse(
        'Error Occurred While Retrieving Account Types: ' . $e->getMessage(),
        [],
        500
      );
    }
  }

  public function store(StoreAccountRequest $request)
  {
    try {
      $data = $request->validated();

      // If account_type_id not explicitly sent, infer from parent
      if (empty($data['account_type_id']) && !empty($data['parent_id'])) {
        $parent = Account::find($data['parent_id']);
        if ($parent && $parent->account_type_id) {
          $data['account_type_id'] = $parent->account_type_id;
        }
      }

      $account = Account::create($data);

      return $this->apiResponseFormatter->successResponse(
        'Account Created Successfully',
        $account->load('accountType'),
        201
      );
    } catch (\Exception $e) {
      return $this->apiResponseFormatter->failedResponse(
        'Failed creating account: ' . $e->getMessage(),
        [],
        422
      );
    }
  }

  public function update(int $id, UpdateAccountRequest $request)
  {
    try {
      $account = Account::findOrFail($id);
      $data = $request->validated();
      $account->update($data);

      return $this->apiResponseFormatter->successResponse(
        'Account Updated Successfully',
        $account->load('accountType')
      );
    } catch (\Exception $e) {
      return $this->apiResponseFormatter->failedResponse(
        'Failed updating account: ' . $e->getMessage(),
        [],
        422
      );
    }
  }

  public function destroy(int $id)
  {
    try {
      $account = Account::findOrFail($id);
      if ($account->entryLines()->exists()) {
        return $this->apiResponseFormatter->failedResponse(
          'Cannot delete account with existing transactions',
          [],
          422
        );
      }

      if ($account->children()->exists()) {
        return $this->apiResponseFormatter->failedResponse(
          'Cannot delete account that has sub-accounts',
          [],
          422
        );
      }

      $account->delete();

      return $this->apiResponseFormatter->successResponse(
        'Account Deleted Successfully',
        null
      );
    } catch (\Exception $e) {
      return $this->apiResponseFormatter->failedResponse(
        'Failed deleting account: ' . $e->getMessage(),
        [],
        422
      );
    }
  }
}
