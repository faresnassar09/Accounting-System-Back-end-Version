<?php

use Illuminate\Support\Facades\Route;
use Modules\User\Http\Controllers\AuthenticationController;
use Modules\User\Http\Controllers\ProfileController;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomain;

Route::controller(AuthenticationController::class)
->prefix('v1/auth')
->group(function () {

    Route::post('/login','login');
});

Route::middleware('auth:sanctum',InitializeTenancyByDomain::class)
->controller(ProfileController::class)
->prefix('v1/profile')
->group(function () {
    
    Route::get('/','getUserData');
});



