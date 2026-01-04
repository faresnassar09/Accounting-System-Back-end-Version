<?php

use Illuminate\Support\Facades\Route;
use Modules\Authorization\Http\Controllers\AuthorizationController;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::apiResource('authorizations', AuthorizationController::class)->names('authorization');
});
