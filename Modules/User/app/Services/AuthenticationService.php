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

    public function getdeviceName(){


    return request()->header('User-Agent') ?: 'Unknown Device';

    }

    public function deletePreviousToken($deviceName,$user){


        $user->tokens()->where('name', $deviceName)->delete();

    }

    public function createToken($deviceName,$user){

       return $user->createToken($deviceName)->plainTextToken;
    }


}
