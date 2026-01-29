<?php

namespace Modules\Accounting\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Modules\Accounting\DTO\JournalEntryData;

class JournalEntryRequest extends FormRequest
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
            'header.total_debit' => ['required','numeric'],
            'header.total_credit' => ['required','numeric'],

            'lines.*.account_id' => ['required','exists:accounts,id'],
            'lines.*.description' => ['nullable','max:255'],

 'lines.*.debit' => [
    'numeric',
    'min:0',
    'max:9999999999',
    'required_if:lines.*.credit,0',
],

'lines.*.credit' => [
    'numeric',
    'min:0',
    'max:9999999999',
    'required_if:lines.*.debit,0',
],

        ];
    }


    public function authorize(): bool
    {
        return true;
    }

    public function messages()
    {
        return [ 
            'header.reference.required' => 'Enter A reference For Your Entry ',
            'header.reference.min' => 'Reference Should Contain at Least 1 Charecter or Number',
            'header.reference.max' => "Reference Shouldn't Excide 255 Charecter" ,

            'header.description.required' => 'Enter A Descrption For Your Entry ',
            'header.description.min' => 'Description should be at least 5 char',
            'header.description.max' => 'Description should be less than 255 char',

            'lines.*.account_id.required' => 'Choose An Account',
            'lines.*.account_id.exists' => 'Account Is Not Found',
            'lines.*.debit.required' => 'Enter The Debit Amount',
            'lines.*.debit.required_without' => 'The Entry Must Contain a Credit or Debit Amount',
            'lines.*.debit.max' => 'Maxmum Debit Amount Should be 9999999999',
            'lines.*.debit.min' => 'Debit Amount Should be at Least 1',
            'lines.*.credit.required' => 'Enter The Credit Amount',
            'lines.*.credit.required' => 'Enter The Credit Amount',
            'lines.*.credit.max' => 'Maxmum Cebit Amount Should be 9999999999',
            'lines.*.credit.min' => 'Credit Amount Should be at Least 1',
            'lines.*.credit.required_without' => 'The Entry Must Contain a Credit or Debit Amount',



        ];
    }
}
