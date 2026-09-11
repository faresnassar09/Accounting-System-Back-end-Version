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

                'descendants:id,parent_id,name,number,description,calculated_balance',

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




}
