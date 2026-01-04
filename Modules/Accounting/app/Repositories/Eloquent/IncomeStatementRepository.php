<?php

namespace Modules\Accounting\Repositories\Eloquent;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Modules\Accounting\Models\Account;
use Modules\Accounting\Repositories\Contracts\IncomeStatementRepositoryInterface;

class IncomeStatementRepository implements IncomeStatementRepositoryInterface{

    public function getRevenueExpenseAccounts($startDate,$endDate){

        return Account::whereHas('accountType', function(Builder $query) {
            $query->whereIn('account_group', ['revenues', 'expenses']);
        })
        ->withSum([
            'entryLines as total_debit' => function ($query) use ($startDate, $endDate) {
                $query->whereHas('journalEntry', function ($q) use ($startDate, $endDate) {
                    $q->whereBetween('date', [$startDate, $endDate]);
                });
            }
        ], 'debit')
        ->withSum([
            'entryLines as total_credit' => function ($query) use ($startDate, $endDate) {
                $query->whereHas('journalEntry', function ($q) use ($startDate, $endDate) {
                    $q->whereBetween('date', [$startDate, $endDate]);
                });
            }
        ], 'credit')
        ->with('accountType:id,type')
        ->get();



    }

    }