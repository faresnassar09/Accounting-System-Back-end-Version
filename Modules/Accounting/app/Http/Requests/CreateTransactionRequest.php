<?php

namespace Modules\Accounting\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateTransactionRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [

            'timestamp' => ['required'],
            'description' => ['required','string','min:5','max:255'],
            'total_amount' => ['required','min:1','max:999999999'],
            'parties.sender.*.source_reference' => ['required'],
            'parties.receiver.*.source_reference' => ['required'],

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
