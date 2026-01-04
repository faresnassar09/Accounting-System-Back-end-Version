<?php

use Illuminate\Support\Facades\Route;
use Modules\User\Http\Controllers\AuthenticationController;


Route::get('test',[AuthenticationController::class,'team']);