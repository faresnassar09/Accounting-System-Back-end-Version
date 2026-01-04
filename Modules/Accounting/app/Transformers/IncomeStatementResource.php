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
                'net_sales'         => round($this['net_sales'], 2),
                'operating_revenue' => round($this['operating_revenue'], 2),
                'total_revenue'     => round($this['net_sales'] + $this['operating_revenue'], 2),
                'details' => collect([
                    ['name' => 'Gross Sales', 'value' => round($this['gross_sales'], 2)],
                    ['name' => 'Sales Deductions', 'value' => -round($this['sales_deductions'], 2)],
                ])
                ->merge($this->formatDetails($this['gross_sales_details']))
                ->merge($this->formatDetails($this['sales_deductions_details'])) // true لضرب القيمة في -1
                ->merge($this->formatDetails($this['operating_revenue_details']))
                ->values(),
            ],

            'cost_of_goods_sold' => [
                'cogs_total'        => round($this['cogs_total'], 2),
                'gross_profit' => round($this['gross_profit'], 2),
                'details'      => $this->formatDetails($this['cogs_details']),
            ],

            'operating_activities' => [
                'total_expenses'   => round($this['expenses_total'], 2),
                'operating_income' => round($this['operating_income'], 2),
                'details'          => $this->formatDetails($this['opreating_expenses_details']),
            ],

            'non_operating' => [
                'non_operating_net' => round($this['non_operating_net'], 2),
                'details'    => collect()
                    ->merge($this->formatDetails($this['non_operating_revenue_details']))
                    ->merge($this->formatDetails($this['non_operating_expenses_details'], true))
                    ->values(),
            ],
            
                'income_before_tax'  => round($this['income_before_tax'], 2),

                'taxes' => [

                    'tax_expense_total'  => round($this['tax_expense_total'], 2),
                    'details' => $this->formatDetails($this['tax_expenses_details']),
                ],

            'final_result' => [
                'net_income'         => round($this['net_income'], 2),
            ],
        ];
    }

    /**
     * دالة مساعدة لتنسيق التفاصيل (Details) بشكل موحد
     */
    private function formatDetails($details, $isNegative = false)
    {
        return collect($details)->map(function ($item) use ($isNegative) {
            $value = data_get($item, 'value', 0);
            return [
                'name'  => data_get($item, 'name', 'N/A'),
                'value' => $isNegative ? -round($value, 2) : round($value, 2),
            ];
        });
    }   
 }

