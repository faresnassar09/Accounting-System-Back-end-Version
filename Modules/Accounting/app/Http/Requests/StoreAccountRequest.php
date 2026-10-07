<?php

namespace Modules\Accounting\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAccountRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'            => ['required', 'string', 'min:2', 'max:100'],
            'number'          => ['required', 'string', 'max:50', 'unique:accounts,number'],
            'parent_id'       => ['nullable', 'integer', 'exists:accounts,id'],
            'account_type_id' => ['nullable', 'integer', 'exists:account_types,id'],
            'description'     => ['nullable', 'string', 'max:255'],
        ];
    }
}
