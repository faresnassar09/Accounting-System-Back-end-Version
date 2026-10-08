<?php

namespace Modules\Accounting\Transformers;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class IncomeStatementResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'start_date' => $this['start_date'],
            'end_date'   => $this['end_date'],
    
            'revenues' => [
                'total_revenue'     => round($this['total_revenue'], 2),
                'net_sales'         => round($this['net_sales'], 2),
                'operating_revenue' => round($this['operating_revenue'], 2),
                'details' => collect()
                    ->merge($this->formatDetails($this['gross_sales_details']))
                    ->merge($this->formatDetails($this['sales_deductions_details'], true)) 
                    ->merge($this->formatDetails($this['operating_revenue_details']))
                    ->values(),
            ],
    
            'cost_of_goods_sold' => [
                'total_cogs'   => round($this['total_cogs'], 2),
                'gross_profit' => round($this['gross_profit'], 2),
                'details'      => $this->formatDetails($this['cogs_details']),
            ],
    
            'operating_activities' => [
                'total_expenses'   => round($this['total_expenses'], 2),
                'operating_income' => round($this['operating_income'], 2),
                'details'          => $this->formatDetails($this['operating_expenses_details']),
            ],
    
            'non_operating' => [
                'non_operating_net' => round($this['non_operating_net'], 2),
                'details' => collect()
                    ->merge($this->formatDetails($this['non_operating_revenue_details']))
                    ->merge($this->formatDetails($this['non_operating_expenses_details'], true))
                    ->values(),
            ],
    
            'taxes' => [
                'income_before_tax' => round($this['income_before_tax'], 2),
                'tax_expense_total' => round($this['tax_expense_total'], 2),
                'details'           => $this->formatDetails($this['tax_expenses_details']),
            ],
    
            'final_result' => [
                'net_income' => round($this['net_income'], 2),
                'is_profit'  => $this['net_income'] >= 0,
            ],
        ];
    }

    private function formatDetails($details, $isNegative = false)
    {
        return  $details;

    }   
 }

