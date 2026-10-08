<?php

namespace Modules\Accounting\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAccountRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $accountId = $this->route('id') ?? $this->route('account');

        return [
            'name'            => ['sometimes', 'required', 'string', 'min:2', 'max:100'],
            'number'          => ['sometimes', 'required', 'string', 'max:50', Rule::unique('accounts', 'number')->ignore($accountId)],
            'parent_id'       => ['nullable', 'integer', 'exists:accounts,id'],
            'account_type_id' => ['nullable', 'integer', 'exists:account_types,id'],
            'description'     => ['nullable', 'string', 'max:255'],
        ];
    }
}
