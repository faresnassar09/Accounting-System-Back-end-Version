<?php

namespace Modules\Accounting\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreBankStatementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'account_id'         => ['required', 'integer', 'exists:accounts,id'],
            'statement_date'     => ['required', 'date'],
            'opening_balance'    => ['required', 'numeric'],
            'closing_balance'    => ['required', 'numeric'],
            'filename'           => ['nullable', 'string', 'max:255'],
            'rows'               => ['required', 'array', 'min:1'],
            'rows.*.date'        => ['required', 'date'],
            'rows.*.description' => ['required', 'string', 'max:255'],
            'rows.*.reference'   => ['nullable', 'string', 'max:100'],
            'rows.*.amount'      => ['required', 'numeric'],
        ];
    }
}
