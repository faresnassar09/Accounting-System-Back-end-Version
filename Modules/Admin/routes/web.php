<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Modules\Admin\Http\Controllers\AdminController;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('admins', AdminController::class)->names('admin');
});

Route::get('admin12',function(){

return Inertia::render('Test');
    
});