<?php

namespace Modules\Accounting\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreFixedAssetRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'asset_number'                       => ['required', 'string', 'max:50', 'unique:fixed_assets,asset_number'],
            'name'                               => ['required', 'string', 'max:100'],
            'category'                           => ['nullable', 'string', 'in:equipment,vehicles,furniture,buildings,land,intangible'],
            'purchase_date'                      => ['required', 'date'],
            'purchase_cost'                      => ['required', 'numeric', 'min:0.01'],
            'salvage_value'                      => ['nullable', 'numeric', 'min:0'],
            'useful_life_months'                 => ['required', 'integer', 'min:1'],
            'depreciation_method'                => ['required', 'in:straight_line,declining_balance'],
            'asset_account_id'                   => ['required', 'integer', 'exists:accounts,id'],
            'depreciation_expense_account_id'    => ['required', 'integer', 'exists:accounts,id'],
            'accumulated_depreciation_account_id'=> ['required', 'integer', 'exists:accounts,id'],
            'branch_id'                          => ['nullable', 'integer'],
        ];
    }
}
