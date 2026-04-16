<?php

use Illuminate\Support\Facades\Route;
use Modules\Connect\Http\Controllers\ConnectController;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::apiResource('connects', ConnectController::class)->names('connect');
});
