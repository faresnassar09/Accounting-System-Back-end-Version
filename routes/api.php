<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Modules\Admin\Models\Admin;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomain;






// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');


// Route::middleware([
//     'web',
//     // PreventAccessFromCentralDomains::class,
// ])->group(function () {
//     Route::get('/testapi', function () {
//         return auth()->user();
//     });
// Route::get('test100',function (){ return auth('admin')->user();});

// });