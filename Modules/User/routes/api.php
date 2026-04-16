<?php

use Illuminate\Support\Facades\Route;
use Modules\User\Http\Controllers\AuthenticationController;
use Modules\User\Http\Controllers\ProfileController;

Route::controller(AuthenticationController::class)
->prefix('v1/auth')
->group(function () {

    Route::post('login','login');
    Route::post('logout','logout')->middleware('auth:api');
});

Route::middleware('auth:api',)
->controller(ProfileController::class)
->prefix('v1/profile')
->group(function () {
    
    Route::get('/','getUserData');
});



