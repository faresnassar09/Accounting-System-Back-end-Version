<?php

namespace Modules\Accounting\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreBudgetRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'fiscal_year'      => ['required', 'integer', 'min:2020', 'max:2099'],
            'account_id'       => ['required', 'integer', 'exists:accounts,id'],
            'branch_id'        => ['nullable', 'integer'],
            'allocated_amount' => ['required', 'numeric', 'min:0'],
            'notes'            => ['nullable', 'string', 'max:500'],
        ];
    }
}
