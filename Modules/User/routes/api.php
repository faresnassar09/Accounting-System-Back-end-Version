<?php

use Illuminate\Support\Facades\Route;
use Modules\User\Http\Controllers\AuthenticationController;
use Modules\User\Http\Controllers\ProfileController;


Route::middleware('auth:sanctum')
->controller(AuthenticationController::class)
->prefix('v1/auth')
->group(function () {

    Route::post('/login','login')->withoutMiddleware('auth:sanctum');
});

Route::middleware('auth:sanctum')
->controller(ProfileController::class)
->prefix('v1/profile')
->group(function () {

    Route::get('/','getUserData');
});



