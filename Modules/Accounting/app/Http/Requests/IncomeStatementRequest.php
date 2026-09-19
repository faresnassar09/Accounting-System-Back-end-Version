<?php

namespace Modules\Accounting\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class IncomeStatementRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'startDate'   => ['nullable', 'date'],
            'start_date'  => ['nullable', 'date'],
            'endDate'     => ['nullable', 'date'],
            'end_date'    => ['nullable', 'date'],
            'export'      => ['nullable', 'string', 'in:pdf,excel'],
            'email'       => ['nullable', 'email'],
            'send_email'  => ['nullable'],
            'branch_id'   => ['nullable', 'integer', 'exists:branches,id'],
            'branchId'    => ['nullable', 'integer', 'exists:branches,id'],
            'queue'       => ['nullable'],
        ];
    }

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }
}
