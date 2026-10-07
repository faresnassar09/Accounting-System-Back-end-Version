<?php

namespace Modules\Accounting\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateFixedAssetRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'     => ['sometimes', 'string', 'max:100'],
            'category' => ['nullable', 'string', 'max:50'],
            'status'   => ['sometimes', 'in:active,fully_depreciated,disposed'],
        ];
    }
}
