<?php

namespace Modules\User\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AuthenticationRequest extends FormRequest
{

    public function rules(): array
    {
        return [

            'email' => ['email','max:50'],
            'password' => ['min:8','max:32'],
        ];
    }


    public function authorize(): bool
    {
        return true;
    }

}
