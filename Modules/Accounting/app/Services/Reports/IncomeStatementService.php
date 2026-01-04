<?php

namespace Modules\Accounting\Services\Reports;

use App\Services\Api\ApiResponseFormatter;
use App\Services\Logging\LoggerService;
use Modules\Accounting\Repositories\Contracts\IncomeStatementRepositoryInterface
 as IncomeStatementInterface;
use Modules\Accounting\Transformers\IncomeStatementResource;

class IncomeStatementService{


    public function __construct( 

        public IncomeStatementInterface $incomeStatementInterface,
        public ApiResponseFormatter $apiResponseFormatter,
        public LoggerService $loggerService,
        
        ){}

    public function generateReport($startDate,$endDate){

        try {
            
                        $accounts =  $this->incomeStatementInterface
          ->getRevenueExpenseAccounts($startDate,$endDate);

          $processedData =  $this->processIncomeStatementData($accounts);


          $netSales = $processedData['gross_sales'] - $processedData['sales_deductions'];

          $totalOperatingRevenue = $netSales + $processedData['operating_revenue'];
          $grossProfit = $totalOperatingRevenue - $processedData['cogs'];
          
          $operatingIncome = $grossProfit - $processedData['operating_expenses'];
          

          $nonOperatingNet = $processedData['non_operating_revenue'] - $processedData['non_operating_expenses'];
          
          $incomeBeforeTax = $operatingIncome + $nonOperatingNet;
          
          $netIncome = $incomeBeforeTax - $processedData['income_tax_expenses'];
          

        $reportData = [

            'start_date' => $startDate,
            'end_date'   => $endDate,

            'net_sales' => $netSales,
            'operating_revenue' => $processedData['operating_revenue'],

                   'gross_sales' => $processedData['gross_sales'],
                   'sales_deductions' => $processedData['sales_deductions'],
                'gross_sales_details' => $processedData['gross_sales_details'],
                'sales_deductions_details' => $processedData['sales_deductions_details'],
                'operating_revenue_details' => $processedData['operating_revenue_details'],

                'cogs_total'   => $processedData['cogs'],
                'gross_profit' => $grossProfit,
                'cogs_details'      => $processedData['cogs_details'],

                'expenses_total'   => $processedData['operating_expenses'],
                'operating_income' => $operatingIncome,
                'opreating_expenses_details' => $processedData['operating_expenses_details'],


                'non_operating_net' => $nonOperatingNet,

                    'non_operating_revenue_details' => $processedData['non_operating_revenue_details'],
                    'non_operating_expenses_details' => $processedData['non_operating_expenses_details'],

           'income_before_tax' => $operatingIncome - $nonOperatingNet,
        
                'tax_expense_total' => $processedData['income_tax_expenses'],
                'tax_expenses_details' => $processedData['income_tax_expenses_details'] ?? [],
        
                'net_income' => $netIncome,
        ];
        
        
          return $this->apiResponseFormatter->successResponse(
            
            'Income Statment Report Generated Successfully',
            
            new IncomeStatementResource($reportData),
            
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
                500
            );


        }
        


    }

    private function processIncomeStatementData($accounts) {

        $accounts =  $this->calculateNetBalanceAndDefineAccountType($accounts);

        $classifications = [
            'gross_sales', 'sales_deductions','operating_revenue', 'non_operating_revenue',
             'cogs', 
            'operating_expenses', 'non_operating_expenses', 'income_tax_expenses'
        ];
    
        foreach ($classifications as $group) {
            
            $groupAccounts = $accounts->where('type', $group);
    
            $results[$group] = $groupAccounts->sum('netBalance');
    
            $results[$group . '_details'] = $groupAccounts->values(); 
        }
    
        return $results;
       
   }

   private function calculateNetBalanceAndDefineAccountType($accounts){

    return $accounts->map(function($query){

        $totalDebit = $query->total_debit;
        $totalCredit = $query->total_credit;
        $type = $query->accountType->type;
        $creditTypes = [
            'gross_sales',
            'operating_revenue',
            'non_operating_revenue'
        ];
    
        if (in_array($type, $creditTypes)) {
            $netBalance = $totalCredit - $totalDebit; 
        } else {
            $netBalance = $totalDebit - $totalCredit; 
        }
        return [

            'id' => $query->id,
            'name' => $query->name,
            'type' =>  $type,
            'netBalance' => $netBalance ?? 0,
            
        ];


    });

   }



}