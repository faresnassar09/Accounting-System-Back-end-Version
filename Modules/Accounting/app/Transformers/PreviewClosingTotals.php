<?php

namespace Modules\Accounting\Transformers;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PreviewClosingTotals extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'total_revenues' => $this['total_revenues'],
            'total_expenses' => $this['total_expenses'],
            'net_profit' => $this['net_profit'],
        ];
    }
}
