<?php

namespace Modules\Accounting\Services\Reports;

use App\Services\Api\ApiResponseFormatter;
use App\Services\Logging\LoggerService;
use Illuminate\Support\Facades\Log;
use Modules\Accounting\Repositories\Contracts\AccountRepositoryInterface as AccountInterface; 
use Modules\Accounting\Repositories\Contracts\TrialBalanceRepositoryInterface as TrialBalnceInterface;
use Modules\Accounting\Transformers\TrialBalanceResource;

class TrialBalanceService{

    public function __construct(
        public AccountInterface $accountInterface,
        public ApiResponseFormatter $apiResponseFormatter,
        public TrialBalnceInterface $trialBalnceInterface,
        public LoggerService $loggerService,
        ){}


    public function generateReport($endDate){

        try {

           $reportData = $this->trialBalnceInterface
            ->calculateAccountsTotals($endDate)->map(function($query){
 
                $total_debit =  $query->total_debit;
                $total_credit = $query->total_credit;
                $netBalance = $total_debit - $total_credit;
    
                $query->final_debit_balance = $netBalance > 0 ? abs($netBalance) : 0.00;
                $query->final_credit_balance = $netBalance < 0 ? abs($netBalance) : 0.00;
    
                return $query;
            });

            $totalGrandDebit = $reportData->sum('final_debit_balance');
            
            $totalGrandCredit = $reportData->sum('final_credit_balance');

            $balanced = $totalGrandDebit === $totalGrandCredit;


            return $this->apiResponseFormatter->successResponse(

                'Trial Balance Report Generated Successfully',
                
                new TrialBalanceResource(

                 [
                    
                    'endDate' => $endDate,
                    'reportData' => $reportData,
                    'totals' =>[
                        
                        'total_debit' => $totalGrandDebit,
                        'total_credit' => $totalGrandCredit,
                        'isBalanced' => $balanced,

                    ],

                    ] ,
                ),

 
            );
            
            

        } catch (\Exception $e) {


            $this->loggerService->failedLogger(

                'Error Occurred While Generating Trial Balance Report',
                [],
                $e->getMessage()
            );

            return $this->apiResponseFormatter->failedResponse(

                'Failed To Generate Trial Balance Report',
                [],
                500,
            );
        }

    }


}