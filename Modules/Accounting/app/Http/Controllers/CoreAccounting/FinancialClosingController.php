<?php

namespace Modules\Accounting\Http\Controllers\CoreAccounting;

use App\Http\Controllers\Controller;
use App\Services\Api\ApiResponseFormatter;
use App\Services\Logging\LoggerService;
use Modules\Accounting\Http\Requests\ClosingFinancialYearRequest;
use Modules\Accounting\Services\CoreAccounting\FinancialClosingService;
use Modules\Accounting\Transformers\PreviewClosingTotals;

class FinancialClosingController extends Controller
{

    public function __construct(

        public FinancialClosingService $financialClosingService,
        public ApiResponseFormatter $apiResponseFormatter,
        public LoggerService $loggerService,
    ) {}

    /**
   * 
   * retrieve revenues and expenses accounts
   *
   * @group financial year
   */

    public function getRevenuesAndExpenses($year)
    {

        try {

            $data = $this->financialClosingService->getRevenuesAndExpenses($year);

            return $this->apiResponseFormatter->successResponse(

                'Revenues And Expenses Total Retrieved Successfully',
                
                new PreviewClosingTotals($data)
            );
        } catch (\Exception $e) {

            $this->loggerService->failedLogger(

                'Failed To Calculate Closing Revenues And Expenses Totals',
                [],
                $e->getMessage(),
            );

            return $this->apiResponseFormatter->failedResponse(

                'Failed To Retrieve Revenues And Expenses Totals',
                [],

            );
        }
    }


    /**
   * 
   * process clsing financial year 
   *
   * @group financial year
   */


    public function applyClosingFinancialYear(ClosingFinancialYearRequest $data)
    {

        \Log::info('joij',[$data->all()]);

        $year = $data['year'];

        try {

         $this->financialClosingService->applyClosingFinancialYear($data);

           return  $this->apiResponseFormatter->successResponse(

            "Financial Year ($year) Closed Successfully",
            [],

           );

        } 

            catch (\Exception $e) {

                $this->loggerService->failedLogger(
    
                    "Closing Year ($year) Failed",
                    [],
                    $e->getMessage()
                );
            }

        

    }
}
