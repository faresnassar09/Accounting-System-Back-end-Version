<?php

namespace Modules\Accounting\Transformers;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TrialBalanceResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'end_date' => $this['endDate'],
            'reportData' => TrialBalanceAccountResource::collection($this['reportData']),
    
            'totals' => [
                'total_debit' => (float) $this['totals']['total_debit'] ?? 0.00,
                'total_credit' => (float) $this['totals']['total_credit'] ?? 0.00,
                
                'isBalanced' => (bool) $this['totals']['isBalanced'] ?? false, 
            ],
        ];
    }
}
