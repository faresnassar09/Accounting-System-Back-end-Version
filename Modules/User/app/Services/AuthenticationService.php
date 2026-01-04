<?php

namespace Modules\User\Services;

use Illuminate\Support\Facades\Auth;


class AuthenticationService
{

    public function attempToLogin($data)
    {

        if (Auth::attempt($data)) {


            return true;
        } else {
            return false;
        }
    }


}
