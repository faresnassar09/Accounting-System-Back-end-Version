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


// Chart Accounting

Route::middleware(['auth:sanctum','role:accountant'])
->controller(AccountingChartController::class)
->prefix('v1/accounting/')
->name('accounting')
->group(function(){

    Route::get('chart-accounting','getAccountingChart');
    Route::get('get-accounts-list','getAccounts');
    Route::get('getClosigAccounts','getClosingAccounts');

});


// Journal Entries

Route::middleware(['auth:sanctum','role:accountant','check_year'])
->prefix('v1/accounting/')
->name('accounting')
->group(function(){

    Route::post('store-journal-entries',[JournalEntriesController::class,'store']);
    Route::post('store-opening-balance',[OpeningBalanceController::class,'store']);
});

Route::middleware(['auth:sanctum','role:accountant'])
->controller(FinancialClosingController::class)
->prefix('v1/accounting/financial-closing/')
->name('accounting')
->group(function(){

    Route::get('preview-details/{year}','getRevenuesAndExpenses');
    Route::post('close','applyClosingFinancialYear');
});

// General Ledger

Route::middleware(['auth:sanctum','role:accountant'])
->controller(GeneralLedgerController::class)
->prefix('v1/accounting/reports/')
->name('accounting')
->group(function(){

    Route::post('general-ledger','generateReport');

});

// Trial Balance

Route::middleware(['auth:sanctum','role:accountant'])
->controller(TrialBalanceController::class)
->prefix('v1/accounting/reports/')
->name('accounting')
->group(function(){

    Route::post('trial-balance','generateReport');

});


// Income Statement

Route::middleware(['auth:sanctum','role:accountant'])
->controller(IncomeStatementController::class)
->prefix('v1/accounting/reports/')
->name('accounting')
->group(function(){

    Route::post('income-statement','generateReport');

});



// Balance Sheet

Route::middleware(['auth:sanctum','role:accountant'])
->controller(BalanceSheetController::class)
->prefix('v1/accounting/reports/')
->name('accounting')
->group(function(){

    Route::post('balance-sheet','generateReport');

});