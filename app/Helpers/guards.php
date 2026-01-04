<?php

use Illuminate\Support\Facades\Auth;



if (!function_exists('get_current_guard')) {


    function get_current_guard(){


        $guards = ['web','admin'];
        foreach ($guards as $guard) {
            if (Auth::guard($guard)->check()) {
                return  $guard;
            }
        }

        return null;
    }


}


if (!function_exists('current_guard_user')) {

    function current_guard_user(): ?\Illuminate\Contracts\Auth\Authenticatable
    {
        $guards = array_keys(config('auth.guards'));

        foreach ($guards as $guard) {
            if (Auth::guard($guard)->check()) {
                return Auth::guard($guard)->user();
            }
        }

        return null; 
    }

 if (!function_exists('all_guards')) {

function all_guards(){

    $guards = array_keys(config('auth.guards'));

    return $guards; 


}

}

}     