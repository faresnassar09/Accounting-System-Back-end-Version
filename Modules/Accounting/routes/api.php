<?php

use Illuminate\Support\Facades\Route;
use Modules\Accounting\Enums\AccountingMappingType;
use Modules\Accounting\Http\Controllers\CoreAccounting\AccountingChartController;
use Modules\Accounting\Http\Controllers\CoreAccounting\BankStatementController;
use Modules\Accounting\Http\Controllers\CoreAccounting\BudgetController;
use Modules\Accounting\Http\Controllers\CoreAccounting\FinancialClosingController;
use Modules\Accounting\Http\Controllers\CoreAccounting\FixedAssetController;
use Modules\Accounting\Http\Controllers\CoreAccounting\JournalEntriesController;
use Modules\Accounting\Http\Controllers\CoreAccounting\OpeningBalanceController;
use Modules\Accounting\Http\Controllers\CoreAccounting\RecurringJournalEntryController;
use Modules\Accounting\Http\Controllers\Reports\BalanceSheetController;
use Modules\Accounting\Http\Controllers\Reports\BudgetVsActualController;
use Modules\Accounting\Http\Controllers\Reports\CashFlowStatementController;
use Modules\Accounting\Http\Controllers\Reports\GeneralLedgerController;
use Modules\Accounting\Http\Controllers\Reports\IncomeStatementController;
use Modules\Accounting\Http\Controllers\Reports\TrialBalanceController;


Route::middleware([

'auth:api',
'throttle:api_limiter'

])
    ->prefix('v1/accounting')
    ->group(function () {


        Route::controller(AccountingChartController::class)->group(function () {
            Route::get('charts', 'getAccountingChart'); 
            Route::get('accounts', 'getAccounts');
            Route::get('accounts/closing', 'getClosingAccounts');
            Route::get('account-types', 'getAccountTypes');
            Route::post('accounts', 'store');
            Route::put('accounts/{id}', 'update');
            Route::delete('accounts/{id}', 'destroy');
        });

        Route::get('journal-entries', [JournalEntriesController::class, 'index']);
        Route::get('journal-entries/{id}', [JournalEntriesController::class, 'show']);
        Route::post('journal-entries/{id}/reverse', [JournalEntriesController::class, 'reverse']);

        Route::middleware(['check_year','ensure_idempotency'])->group(function () {
            Route::post('journal-entries', [JournalEntriesController::class, 'store']);
            Route::post('opening-balances', [OpeningBalanceController::class, 'store']);
        });

        // Budgets
        Route::apiResource('budgets', BudgetController::class);

        // Recurring Journal Entries
        Route::get('recurring-entries', [RecurringJournalEntryController::class, 'index']);
        Route::post('recurring-entries', [RecurringJournalEntryController::class, 'store']);
        Route::get('recurring-entries/{id}', [RecurringJournalEntryController::class, 'show']);
        Route::put('recurring-entries/{id}', [RecurringJournalEntryController::class, 'update']);
        Route::delete('recurring-entries/{id}', [RecurringJournalEntryController::class, 'destroy']);
        Route::post('recurring-entries/{id}/run', [RecurringJournalEntryController::class, 'run']);

        // Fixed Assets
        Route::get('fixed-assets', [FixedAssetController::class, 'index']);
        Route::post('fixed-assets', [FixedAssetController::class, 'store']);
        Route::get('fixed-assets/{id}', [FixedAssetController::class, 'show']);
        Route::put('fixed-assets/{id}', [FixedAssetController::class, 'update']);
        Route::delete('fixed-assets/{id}', [FixedAssetController::class, 'destroy']);
        Route::post('fixed-assets/{id}/depreciate', [FixedAssetController::class, 'depreciate']);

        // Bank Statements & Reconciliations
        Route::get('bank-statements', [BankStatementController::class, 'index']);
        Route::post('bank-statements', [BankStatementController::class, 'store']);
        Route::get('bank-statements/unreconciled-lines', [BankStatementController::class, 'unreconciledLedgerLines']);
        Route::get('bank-statements/{id}', [BankStatementController::class, 'show']);
        Route::post('bank-statements/{id}/auto-match', [BankStatementController::class, 'autoMatch']);
        Route::post('bank-statements/{id}/match-line', [BankStatementController::class, 'matchLine']);
        Route::post('bank-statements/{id}/unmatch-line', [BankStatementController::class, 'unmatchLine']);
        Route::post('bank-statements/{id}/reconcile', [BankStatementController::class, 'reconcile']);

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
            Route::get('cash-flow', [CashFlowStatementController::class, 'generateReport']);
            Route::get('budget-vs-actual', [BudgetVsActualController::class, 'generateReport']);
            Route::get('ar-aging', [\Modules\Accounting\Http\Controllers\Reports\AgingReportController::class, 'arAging']);
            Route::get('ap-aging', [\Modules\Accounting\Http\Controllers\Reports\AgingReportController::class, 'apAging']);
        });

    });
