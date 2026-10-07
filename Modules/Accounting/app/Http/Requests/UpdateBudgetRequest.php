<?php

namespace Modules\Accounting\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateBudgetRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'fiscal_year'      => ['sometimes', 'required', 'integer', 'min:2020', 'max:2099'],
            'account_id'       => ['sometimes', 'required', 'integer', 'exists:accounts,id'],
            'branch_id'        => ['nullable', 'integer'],
            'allocated_amount' => ['sometimes', 'required', 'numeric', 'min:0'],
            'notes'            => ['nullable', 'string', 'max:500'],
        ];
    }
}
