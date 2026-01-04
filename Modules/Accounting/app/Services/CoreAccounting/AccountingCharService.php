<?php

namespace Modules\Accounting\Services\CoreAccounting;

use App\Services\Api\ApiResponseFormatter;
use App\Services\Logging\LoggerService;
use Illuminate\Support\Facades\Auth;
use Modules\Accounting\Repositories\Contracts\AccountRepositoryInterface as ChartInterface;
use Modules\Accounting\Transformers\AccountListResource;

class AccountingCharService{


    public function __construct(
        public ChartInterface $chartInterface,
        public ApiResponseFormatter $apiResponseFormatter,
        public LoggerService $loggerService,

        ){}

    public function viewChartTree(){

        $accountingChart = $this->chartInterface->chartTree();

        return $this->apiResponseFormatter->successResponse(

            'Chart Tree Reterived Successfully',
            $accountingChart,
            

        );


    }

    public function getAccounts()
    {



        try {

            $account = $this->chartInterface->getAccounts();

            return $this->apiResponseFormatter->successResponse(

                'Accounts Retrieved Successfully',
                AccountListResource::collection($account),

            );
        } catch (\Exception $e) {

            $this->loggerService->failedLogger(
                'Error Occurred While Retrieving Accounts',
                ['userId' => Auth::id(),],
                $e->getMessage()

            );

            return $this->apiResponseFormatter->failedResponse(

                'Error Occurred While Retrieving Accounts',
                [],

            );
        }
    }

}