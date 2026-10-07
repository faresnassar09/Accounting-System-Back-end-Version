<?php

namespace Modules\Accounting\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DisposeAssetRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'disposal_amount'       => ['required', 'numeric', 'min:0'],
            'disposal_date'         => ['nullable', 'date'],
            'cash_account_id'       => ['required', 'integer', 'exists:accounts,id'],
            'gain_loss_account_id'  => ['required', 'integer', 'exists:accounts,id'],
        ];
    }
}
