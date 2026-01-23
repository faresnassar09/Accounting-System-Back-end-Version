<?php

namespace Modules\Accounting\Http\Middleware;

use App\Services\Api\ApiResponseFormatter;
use Carbon\Carbon;
use Closure;
use Illuminate\Http\Request;
use Modules\Accounting\Repositories\Contracts\FinancialClosingReposiroryInterface;

class PreventActionOnClosedYearMiddleware
{

    public function __construct(
        
        public FinancialClosingReposiroryInterface $financialClosedRepository,
        public ApiResponseFormatter $apiResponseFormatter){}

    public function handle(Request $request, Closure $next)
    {
            
        $year = Carbon::parse($request->input('header.date'))->format('Y');

        $alreadyClosed = $this->financialClosedRepository->isYearClosed($year);

        if ($alreadyClosed) {

            return $this->apiResponseFormatter->failedResponse(
                
                "Year ($year) Is Closed You Cant't Add Or Edit Journals In it",
                []
                
            );
        }



        return $next($request);
    }
}
