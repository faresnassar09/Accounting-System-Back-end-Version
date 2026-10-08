<?php

use Illuminate\Support\Facades\Route;
use Laravel\Passport\Http\Controllers\AccessTokenController;
use Modules\Admin\Models\Admin;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomain;


Route::middleware([
    InitializeTenancyByDomain::class, 

])->group(function () {
    
Route::post('/oauth/token', [AccessTokenController::class, 'issueToken']);
 
});

