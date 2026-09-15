<?php

use Illuminate\Support\Facades\Route;
use Laravel\Passport\Http\Middleware\EnsureClientIsResourceOwner;
use Modules\Accounting\Http\Controllers\External\Reports\BalanceSheetController;
use Modules\Accounting\Http\Controllers\External\Reports\GeneralLedgerController;
use Modules\Accounting\Http\Controllers\External\Reports\IncomeStatementController;
use Modules\Accounting\Http\Controllers\External\Reports\TrialBalanceController;
use Modules\Accounting\Http\Controllers\External\TransactionController;

Route::middleware([EnsureClientIsResourceOwner::class, 'throttle:external_api'])
    ->prefix('external/')
    ->group(function () {

        Route::controller(TransactionController::class)
        ->prefix('transaction/')
        ->group(function () {
            Route::get('get', 'getTransactions');
            Route::post('create', 'create');
        });

        Route::prefix('reports')
        ->group(function () {
            Route::get('trial-balance', [TrialBalanceController::class, 'generateReport']);
            Route::get('general-ledger', [GeneralLedgerController::class, 'generateReport']);
            Route::get('income-statement', [IncomeStatementController::class, 'generateReport']);
            Route::get('balance-sheet', [BalanceSheetController::class, 'generateReport']);
        });

    });


