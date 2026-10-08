<?php

namespace Modules\Accounting\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Modules\Accounting\Repositories\Contracts\AccountingMappingRepositoryInterface;
use Modules\Accounting\Repositories\Eloquent\AccountRepository;

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

public function withValidator($validator)
{
    $validator->after(function ($validator) {
        $account = app(AccountingMappingRepositoryInterface::class)->getOpeningBalanceAccount();
        if (!$account) {
            $validator->errors()->add('opening_balance', 'You Need To Create An Opening Account to balance the entry * Contact With Your Administrator');
        }
    });
}
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }
}
