<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Modules\Admin\Models\Admin;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomain;
use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;

/*
|--------------------------------------------------------------------------
| Tenant Routes
|--------------------------------------------------------------------------
|
| Here you can register the tenant routes for your application.
| These routes are loaded by the TenantRouteServiceProvider.
|
| Feel free to customize them however you want. Good luck!
|
*/

Route::middleware([
    'web',
    InitializeTenancyByDomain::class,
    // PreventAccessFromCentralDomains::class,
])->group(function () {
    Route::get('/t', function () {
        return Admin::all();
    });
Route::get('test100',function (){ return auth('admin')->user();});
















Route::get('/tenant-file/{path}', function ($path) {

    $tenantId = tenant()->getTenantKey();

    // full path of the file
    $fullPath = storage_path("{$tenantId}/app/public{$path}");

    if (! file_exists($fullPath)) {
        abort(404);
    }

    // Stream the file back to the browser (preview/download)
    return response()->file($fullPath);

})->where('path', '.*') // allow subfolders
  ->name('tenant.file')
  ->middleware(['web', 'tenancy']); 

});
