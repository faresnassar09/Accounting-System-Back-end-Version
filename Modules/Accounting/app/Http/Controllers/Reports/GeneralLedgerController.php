<?php

namespace Modules\Accounting\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use App\Services\Api\ApiResponseFormatter;
use App\Services\Logging\LoggerService;
use Modules\Accounting\Http\Requests\GeneralLedgerRequest;
use Modules\Accounting\Services\Reports\GeneralLedgerService;
use Modules\Accounting\Transformers\GeneralLedgerResource;

class GeneralLedgerController extends Controller
{

    public function __construct(

        public GeneralLedgerService $generalLedgerService,
        public LoggerService $loggerService,
        public ApiResponseFormatter $apiResponseFormatter,

    ) {}

    
    /**
   * 
   * generate general ledger report 
   *
   * @group general ledger report
   */

    public function generateReport(GeneralLedgerRequest $data)
    {

        try {

            $reportData =  $this->generalLedgerService->generateReport($data);

            return $this->apiResponseFormatter->successResponse(

                'General Ledger Report Generated Successfully',

                new GeneralLedgerResource($reportData),

            );
        } catch (\Exception $e) {

            $this->loggerService->failedLogger(

                'Error Occurred While Generating General Ledger Report',
                [],
                $e->getMessage()
            );

            return $this->apiResponseFormatter->failedResponse(

                'Failed To Generate General Ledger Report',
                [],
                500,
            );
        }
    }
}
