<?php

namespace Modules\Accounting\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TrialBalanceRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [

            'endDate' => ['date'],
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
