<?php

use Illuminate\Support\Facades\Route;
use Modules\Accounting\Http\Controllers\CoreAccounting\AccountingChartController;
use Modules\Accounting\Http\Controllers\CoreAccounting\FinancialClosingController;
use Modules\Accounting\Http\Controllers\CoreAccounting\JournalEntriesController;
use Modules\Accounting\Http\Controllers\CoreAccounting\OpeningBalanceController;
use Modules\Accounting\Http\Controllers\Reports\BalanceSheetController;
use Modules\Accounting\Http\Controllers\Reports\GeneralLedgerController;
use Modules\Accounting\Http\Controllers\Reports\IncomeStatementController;
use Modules\Accounting\Http\Controllers\Reports\TrialBalanceController;

Route::middleware(['auth:sanctum', 'role:accountant'])
    ->prefix('v1/accounting')
    ->group(function () {

        Route::controller(AccountingChartController::class)->group(function () {
            Route::get('charts', 'getAccountingChart'); 
            Route::get('accounts', 'getAccounts');
            Route::get('accounts/closing', 'getClosingAccounts');
        });


        Route::middleware('check_year')->group(function () {
            Route::post('journal-entries', [JournalEntriesController::class, 'store']);
            Route::post('opening-balances', [OpeningBalanceController::class, 'store']);
        });

        Route::controller(FinancialClosingController::class)
            ->prefix('financial-closing')
            ->group(function () {
                Route::get('preview/{year}', 'getRevenuesAndExpenses');
                Route::post('apply', 'applyClosingFinancialYear');
            });

        Route::prefix('reports')->group(function () {
            Route::get('general-ledger', [GeneralLedgerController::class, 'generateReport']);
            Route::get('trial-balance', [TrialBalanceController::class, 'generateReport']);
            Route::get('income-statement', [IncomeStatementController::class, 'generateReport']);
            Route::get('balance-sheet', [BalanceSheetController::class, 'generateReport']);
        });

    });