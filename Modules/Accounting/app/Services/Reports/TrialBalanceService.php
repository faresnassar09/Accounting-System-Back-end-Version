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
            ->calculateAccountsTotals($endDate)
            ->map(function($query){
 
                $total_debit =  $query->total_debit;
                $total_credit = $query->total_credit;
                $netBalance = $total_debit - $total_credit;
    
                $query->period_debit = $netBalance > 0 ? abs($netBalance) : 0.00;
                $query->period_credit = $netBalance < 0 ? abs($netBalance) : 0.00;
    
                $query->final_debit_balance = $query->period_debit +
                $query->opening_debit;

                $query->final_credit_balance = $query->period_credit +
                $query->opening_credit;


                return $query;
            });


            $totalOpeningDebit = $reportData->sum('opening_debit');
            $totalOpeningCredit = $reportData->sum('opening_credit');

            $totalGrandDebit = $reportData->sum('period_debit') + $totalOpeningDebit;
            
            $totalGrandCredit = $reportData->sum('period_credit') + $totalOpeningCredit;




                    //  \Log::info('d',[$totalOpeningDebit]);


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