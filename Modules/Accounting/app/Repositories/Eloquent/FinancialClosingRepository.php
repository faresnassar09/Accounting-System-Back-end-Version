<?php 

namespace Modules\Accounting\Repositories\Eloquent;

use Modules\Accounting\Models\ClosedFinancialYear;
use Modules\Accounting\Repositories\Contracts\FinancialClosingReposiroryInterface;
use Modules\Accounting\Services\Reports\IncomeStatementService;

class FinancialClosingRepository implements FinancialClosingReposiroryInterface{

    public function __construct(public IncomeStatementService $incomeStatmentService){}

    public function isYearClosed($year){

        return ClosedFinancialYear::where('year',$year)
        ->first();

    }
    
    public function flagYearAsClosed($year,$netProfit,$clogingAccountId){

        ClosedFinancialYear::create([

            'closed_by' => current_guard_user()->id,
            'year' => $year,
            'net_profit_loss' => $netProfit,
            'retained_earnings_account_id' => $clogingAccountId, 

        ]);

    }

}