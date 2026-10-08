<?php

namespace Modules\Accounting\Transformers;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GeneralLedgerResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [

            'account_info' => [
                'name' => $this['account_info']['name'] ?? null, // استخدام Null Coalescing في حال غياب الحقل
                'number' => $this['account_info']['number'] ?? null,
            ],
            
            'opening_balance' => (float) $this['opening_balance'],
            'closing_balance' => (float) $this['closing_balance'],
            'total_debit' => (float) $this['total_debit'],
            'total_credit' => (float) $this['total_credit'],

            'transactions' => $this['transactions'], 
        ];    }
}
