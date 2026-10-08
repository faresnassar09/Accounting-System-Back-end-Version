<?php

namespace Modules\Accounting\Transformers;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class JournalEntryListResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'           => $this->id,
            'reference'    => $this->reference,
            'date'         => $this->date?->format('Y-m-d H:i:s') ?? $this->date,
            'description'  => $this->description,
            'type'         => $this->type,
            'status'       => $this->status,
            'branch_id'    => $this->branch_id,
            'branch_name'  => $this->branch?->name,
            'total_debit'  => (float) $this->total_debit,
            'total_credit' => (float) $this->total_credit,
            'lines_count'  => $this->lines_count ?? $this->lines?->count() ?? 0,
            'created_at'   => $this->created_at?->format('Y-m-d H:i:s'),
        ];
    }
}
