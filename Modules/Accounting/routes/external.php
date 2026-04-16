<?php

use Illuminate\Support\Facades\Route;
use Laravel\Passport\Http\Middleware\EnsureClientIsResourceOwner;
use Modules\Accounting\Http\Controllers\External\TransactionController;

Route::middleware(EnsureClientIsResourceOwner::class)->prefix('external/')->group(function(){


    Route::controller(TransactionController::class)->prefix('transaction/')->group(function(){

        Route::get('get','getTransactions');
        Route::post('create','create');

    });


});

