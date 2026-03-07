<?php


use Illuminate\Support\Facades\Route;
use Laravel\Passport\Http\Controllers\AccessTokenController;
use Laravel\Passport\Passport;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomain;
use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;


Route::middleware([
    'api',
    InitializeTenancyByDomain::class, // هنا السر: التينانسي بتشتغل الأول
    PreventAccessFromCentralDomains::class,
])->group(function () {
    
    // تعريف مسارات باسبورت داخل نطاق التينانت
Route::post('/oauth/token', [AccessTokenController::class, 'issueToken']);


});

