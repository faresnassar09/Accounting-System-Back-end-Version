<?php

namespace Modules\Accounting\Http\Requests\External;

use Illuminate\Foundation\Http\FormRequest;

class GetTransaction extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [

            'start_date' => ['date'],
            'end_date' => ['date'],
            'source_reference' => ['string'],
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
