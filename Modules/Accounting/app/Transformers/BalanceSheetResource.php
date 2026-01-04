<?php

namespace Modules\Accounting\Transformers;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BalanceSheetResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {


        return [
            'group_code'  => $this['group_code'],
            'group_name'  => ucfirst($this['group_name']),  
            'group_total' => (float) $this['group_total'],   
            'sub_types'   => $this['sub_types'], 
        ];
    
    
    }
}
