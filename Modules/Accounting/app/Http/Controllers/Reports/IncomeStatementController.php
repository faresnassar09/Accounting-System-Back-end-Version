<?php

namespace Modules\Accounting\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use App\Services\Api\ApiResponseFormatter;
use App\Services\Logging\LoggerService;
use Modules\Accounting\Http\Requests\IncomeStatementRequest;
use Modules\Accounting\Services\Reports\IncomeStatementService;
use Modules\Accounting\Transformers\IncomeStatementResource;

class IncomeStatementController extends Controller
{

    public function __construct(
        public IncomeStatementService $incomeStatementService,
        public ApiResponseFormatter $apiResponseFormatter,
        public LoggerService $loggerService
    ) {}

    /**
   * 
   * generate income statement report 
   *
   * @group income statement report
   */

    public function generateReport(IncomeStatementRequest $request)
    {
        try {
            $startDate = $request->input('startDate') ?? $request->input('start_date');
            $endDate   = $request->input('endDate') ?? $request->input('end_date');

            $data = $this->incomeStatementService->generateReport($startDate, $endDate);

            return $this->apiResponseFormatter->successResponse(
                'Income Statment Report Generated Successfully',
                new IncomeStatementResource($data),
            );
        } catch (\Exception $e) {

            $this->loggerService->failedLogger(

                'Error Occurred While Generating Income Statement Report',
                [],
                $e->getMessage()
            );

            return $this->apiResponseFormatter->failedResponse(

                'Failed To Genereate Income Statement Report',
                [],
        
            );
        }
    }
}
