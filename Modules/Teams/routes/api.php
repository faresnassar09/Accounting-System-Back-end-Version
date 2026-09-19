<?php

use Illuminate\Support\Facades\Route;
use Modules\Teams\Http\Controllers\TeamsController;

Route::middleware(['auth:api', 'throttle:api_limiter'])
    ->prefix('v1/teams')
    ->group(function () {
        Route::get('/', [TeamsController::class, 'index']);
        Route::get('{id}', [TeamsController::class, 'show']);
    });
