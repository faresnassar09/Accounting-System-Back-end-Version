<?php

namespace Modules\Accounting\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UnreconciledLedgerLinesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'account_id' => ['required', 'integer', 'exists:accounts,id'],
            'as_of_date' => ['nullable', 'date'],
        ];
    }
}
