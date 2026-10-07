<?php

namespace Modules\Accounting\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreRecurringJournalEntryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'reference_template' => ['required', 'string', 'max:50'],
            'description'        => ['required', 'string', 'max:255'],
            'frequency'          => ['required', 'in:daily,weekly,monthly,quarterly,yearly'],
            'start_date'         => ['required', 'date'],
            'end_date'           => ['nullable', 'date', 'after_or_equal:start_date'],
            'auto_post'          => ['boolean'],
            'branch_id'          => ['nullable', 'integer'],
            'currency_code'      => ['nullable', 'string', 'max:10'],
            'exchange_rate'      => ['nullable', 'numeric', 'min:0'],
            'lines'              => ['required', 'array', 'min:2'],
            'lines.*.account_id' => ['required', 'integer', 'exists:accounts,id'],
            'lines.*.debit'      => ['required', 'numeric', 'min:0'],
            'lines.*.credit'     => ['required', 'numeric', 'min:0'],
            'lines.*.description'=> ['nullable', 'string', 'max:255'],
            'lines.*.branch_id'  => ['nullable', 'integer'],
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $lines = $this->input('lines', []);
            $totalDebit = round(collect($lines)->sum('debit'), 2);
            $totalCredit = round(collect($lines)->sum('credit'), 2);

            if (abs($totalDebit - $totalCredit) > 0.001) {
                $validator->errors()->add('lines', "Recurring entry must be balanced. Total Debit ($totalDebit) does not equal Total Credit ($totalCredit).");
            }

            if ($totalDebit <= 0) {
                $validator->errors()->add('lines', 'Total debit must be greater than zero.');
            }
        });
    }
}
