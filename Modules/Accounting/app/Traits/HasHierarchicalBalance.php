<?php

namespace Modules\Accounting\Traits;

use Modules\Accounting\Models\Account;
use Modules\Accounting\Models\AccountType;
use Modules\Accounting\Models\JournalEntryLine;

trait HasHierarchicalBalance {

    public function parent()
    {

        return $this->belongsTo(Account::class,'parent_id');
    }

    

    public function accountType()
    {

        return $this->belongsTo(AccountType::class);
    }



    public function children()
    {

        return $this->hasMany(Account::class, 'parent_id');
    }

    public function descendants()
    {

        return $this->hasMany(Account::class, 'parent_id')
            ->with(

                'descendants:id,parent_id,name,number,description,calculated_balance,descendants_count',

                'accountType:id,type'
            );
    }



    public function scopeAccountTyps($query)
    {

        return $query->whereNull('parent_id');
    }

    public function entryLines()
    {

        return $this->hasMany(JournalEntryLine::class);
    }

    public function recalculateParentBalances()
    {

        $children_balance = $this->children()->sum('calculated_balance');

        $newCalculatedBalance = $this->calculated_balance + $children_balance;

        if ($this->calculated_balance !== $newCalculatedBalance) {

            $this->calculated_balance =  $newCalculatedBalance;
            $this->saveQuietly();
        }


        if ($this->parent) {

            $this->parent->recalculateParentBalances();
        }
    }

    public function recalculateDescendantsCount()
    {

        $childCount = $this->children()->count();
        $descendantsCount = $this->children()->sum('descendants_count');
        $newDescendantsCount = $childCount + $descendantsCount;

        if ($this->descendants_count !== $newDescendantsCount) {

            $this->descendants_count = $newDescendantsCount;
            $this->saveQuietly();
        }

        if ($this->parent) {

            $this->parent->recalculateDescendantsCount();
        }
    }

    public function propagateBalanceChange($amount){

        $this->incrementQuietly('calculated_balance',$amount);

        if ($this->account_id) {

            $this->parent->propagateBalanceChange($amount);

        }

    }


}
