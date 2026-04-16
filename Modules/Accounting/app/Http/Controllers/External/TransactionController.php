<?php

namespace Modules\Accounting\Http\Controllers\External;

use App\Http\Controllers\Controller;
use App\Services\Api\ApiResponseFormatter;
use App\Services\Logging\LoggerService;
use Modules\Accounting\Http\Requests\CreateTransactionRequest;
use Modules\Accounting\Http\Requests\External\GetTransaction;
use Modules\Accounting\Services\External\TransactionService;

class TransactionController extends Controller
{

    public function __construct(
        public TransactionService $transactionService,
        public ApiResponseFormatter $apiFormatter,
        public LoggerService $loggerService,

    ) {}

    public function getTransactions(GetTransaction $data)
    {

        try {
            
            return $this->transactionService->getTransactions($data->validated());
            
        } catch (\Exception $e) {

        }

    }

    public function create(CreateTransactionRequest $data)
    {


        try {

            $this->transactionService->create($data);

            return $this->apiFormatter->successResponse(

                'Transaction Created Successfully',
                [],
                201,
            );
        } catch (\Exception $e) {


            $this->loggerService->failedLogger(

                'Failed To Create Transaction',
                [],
                $e->getMessage(),
            );

            return $this->apiFormatter->failedResponse(

                'Failed To Create Transaction',
                [],
                500
            );
        }
    }

    
}
