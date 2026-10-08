<?php

namespace Modules\Accounting\Transformers;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ChartAccountingResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {

        $resourceClass = static::class;


        return [

            'name' => $this->name,
            'number' => $this->number,
            'description' => $this->description,
            'calculated_balance' => $this->calculated_balance,
            'descendants_count' => $this->descendants_count,
            'accountType' => $this->accountType->type,


            //Retrive All Descendants And Ensoure we avoid N+1 problem 
            // That We'll Rebuild The current Resource For Each Child
 
            'descendants' => $this->whenLoaded('children', function () use ($resourceClass) {

                return $resourceClass::collection($this->children);
            }),





        ];
    }
}
