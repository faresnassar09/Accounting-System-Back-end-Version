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
            'parties.senders.*.source_reference' => ['required'],
            'parties.receivers.*.source_reference' => ['required'],

        ];
    }

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $totalAmount = floatval($this->input('total_amount', 0));
            $senders = $this->input('parties.senders', []);
            $receivers = $this->input('parties.receivers', []);

            $totalSenderAmount = collect($senders)->sum(fn($sender) => floatval($sender['amount'] ?? 0));
            $totalReceiverAmount = collect($receivers)->sum(fn($receiver) => floatval($receiver['amount'] ?? 0));

            if (abs($totalSenderAmount - $totalAmount) > 0.001) {
                $validator->errors()->add('parties.sender', 'The sum of sender amounts must equal the total amount.');
            }

            if (abs($totalReceiverAmount - $totalAmount) > 0.001) {
                $validator->errors()->add('parties.receiver', 'The sum of receiver amounts must equal the total amount.');
            }
        });
    }
}
