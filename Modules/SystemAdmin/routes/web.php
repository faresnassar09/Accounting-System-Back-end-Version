<?php

use Illuminate\Support\Facades\Route;
use Modules\SystemAdmin\Http\Controllers\SystemAdminController;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('systemadmins', SystemAdminController::class)->names('systemadmin');
});
