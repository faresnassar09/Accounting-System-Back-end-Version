<?php

namespace Modules\Accounting\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
class GeneralLedgerRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [

            'accountId' => ['required', 'exists:accounts,id'],
            'startDate' => ['nullable','date'],
            'endDate' => ['nullable','date'],
        ];
    }

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    } 

    public function messages()
    {

        return [

            'accountId.required' => 'Chose An Account From The list',
            'accountId.exists' => 'This Account Is Not Found',

        ];
    }
}
