<?php

use Illuminate\Support\Facades\Route;
use Modules\SystemAdmin\Http\Controllers\SystemAdminController;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::apiResource('systemadmins', SystemAdminController::class)->names('systemadmin');
});
