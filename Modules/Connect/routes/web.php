<?php

use Illuminate\Support\Facades\Route;
use Modules\Connect\Http\Controllers\ConnectController;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('connects', ConnectController::class)->names('connect');
});
