<?php

namespace Modules\Accounting\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class OpeningBalanceRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [

            'header.reference' => ['required','min:1','max:255'],
            'header.date' => ['nullable','date'],
            'header.description' => ['required','min:5','max:255'],


            'lines.*.account_id' => ['required','exists:accounts,id'],
            
 'lines.*.debit' => [
    'required',
    'numeric',
    'min:0',
    'max:9999999999',
    'required_if:lines.*.credit,0',
],

'lines.*.credit' => [
    'required',
    'numeric',
    'min:0',
    'max:9999999999',
    'required_if:lines.*.debit,0',
],
            
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
