<?php

namespace Modules\Accounting\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRecurringJournalEntryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'description' => ['sometimes', 'string', 'max:255'],
            'frequency'   => ['sometimes', 'in:daily,weekly,monthly,quarterly,yearly'],
            'status'      => ['sometimes', 'in:active,paused,completed'],
            'auto_post'   => ['sometimes', 'boolean'],
            'end_date'    => ['nullable', 'date'],
        ];
    }
}
