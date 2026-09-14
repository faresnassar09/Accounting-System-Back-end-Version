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
            'id' => data_get($this->resource, 'id'),
            'number' => data_get($this->resource, 'number'),
            'name' => data_get($this->resource, 'name'),
            'period_debit' => (float) data_get($this->resource, 'period_debit'),
            'period_credit' => (float) data_get($this->resource, 'period_credit'),
            'total_opening_debit' => (float) data_get($this->resource, 'opening_debit'),
            'total_opening_credit' => (float) data_get($this->resource, 'opening_credit'),
            'final_credit_balance' => (float) data_get($this->resource, 'final_credit_balance'),
            'final_debit_balance' => (float) data_get($this->resource, 'final_debit_balance'),
        ];
    
    }
}
