<?php

namespace Modules\Accounting\Services\CoreAccounting;

use App\Services\Api\ApiResponseFormatter;
use App\Services\Logging\LoggerService;
use Modules\Accounting\Repositories\Contracts\AccountRepositoryInterface as ChartInterface;

class AccountingCharService{


    public function __construct(
        public ChartInterface $chartInterface,
        public ApiResponseFormatter $apiResponseFormatter,
        public LoggerService $loggerService,

        ){}

    public function viewChartTree(){

       return $this->chartInterface->chartTree();


    }

    public function getAccounts()
    {
       return $this->chartInterface->getAllAccounts();

    }

    public function getClosingAccounts(){
        
        return $this->chartInterface->getClosingAccounts();

    }

}