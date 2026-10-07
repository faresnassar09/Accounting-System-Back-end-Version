<?php

namespace Modules\Accounting\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MatchLineRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'statement_line_id'      => ['required', 'integer', 'exists:bank_statement_lines,id'],
            'journal_entry_line_id'  => ['required', 'integer', 'exists:journal_entry_lines,id'],
        ];
    }
}
