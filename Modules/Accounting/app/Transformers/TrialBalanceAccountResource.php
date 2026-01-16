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
            'period_debit' => (float) $this['period_debit'],
            'period_credit' => (float) $this['period_credit'],
            'total_opening_debit' => (float) $this['opening_debit'],
            'total_opening_credit' => (float) $this['opening_credit'],
            'final_credit_balance' => $this['final_credit_balance'],
            'final_debit_balance' => $this['final_debit_balance'],

        ];
    
    }
}
