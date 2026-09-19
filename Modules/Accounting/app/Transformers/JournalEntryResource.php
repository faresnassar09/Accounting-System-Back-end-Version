<?php

namespace Modules\Accounting\Transformers;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class JournalEntryResource extends JsonResource
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
            'total_debit'  => (float) $this->total_debit,
            'total_credit' => (float) $this->total_credit,
            'created_at'   => $this->created_at?->format('Y-m-d H:i:s'),
            'lines'        => $this->lines?->map(function ($line) {
                return [
                    'id'               => $line->id,
                    'account_id'       => $line->account_id,
                    'account_name'     => $line->account?->name,
                    'account_number'   => $line->account?->number,
                    'debit'            => (float) $line->debit,
                    'credit'           => (float) $line->credit,
                    'source_type'      => $line->source_type,
                    'source_reference' => $line->source_reference,
                    'date'             => $line->date,
                ];
            }),
        ];
    }
}
