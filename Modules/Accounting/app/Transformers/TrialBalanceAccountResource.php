<?php

namespace Modules\Accounting\Transformers;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TrialBalanceAccountResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this['id'],
            'number' => $this['number'],
            'name' => $this['name'],
            'final_debit_balance' => (float) $this['final_debit_balance'],
            'final_credit_balance' => (float) $this['final_credit_balance'],
            
        ];
    
    }
}
