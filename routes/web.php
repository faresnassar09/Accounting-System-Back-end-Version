<?php

use Illuminate\Support\Facades\Route;
use Laravel\Passport\Http\Controllers\AccessTokenController;
use Laravel\Passport\Http\Middleware\EnsureClientIsResourceOwner;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomain;



Route::get('login',function(){

    return 'bad try';
})->name('login');


