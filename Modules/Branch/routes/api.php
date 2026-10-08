<?php

use Illuminate\Support\Facades\Route;
use Modules\Branch\Http\Controllers\BranchController;

Route::middleware(['auth:api', 'throttle:api_limiter'])
    ->prefix('v1/branches')
    ->group(function () {
        Route::get('/', [BranchController::class, 'index']);
        Route::get('{id}', [BranchController::class, 'show']);
    });
