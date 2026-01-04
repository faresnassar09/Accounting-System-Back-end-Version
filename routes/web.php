<?php

use App\Models\Tenant;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Modules\Branch\Models\Branch;
use Inertia\Inertia;


Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

foreach (config('tenancy.central_domains') as $domain) {
    Route::domain($domain)->group(function () {

    Route::get('t12',function(){
        
        
        
        return Tenant::all(); });


    });
}


Route::middleware([
    'auth:web',
    config('jetstream.auth_session'),
])->group(function () {
    Route::get('dashboard', function () {
        return redirect('/');
        })->name('dashboard');
});
