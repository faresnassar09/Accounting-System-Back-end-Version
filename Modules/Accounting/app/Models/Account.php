<?php

namespace Modules\Accounting\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\Accounting\Database\Factories\AccountFactory;
use Modules\Accounting\Traits\HasHierarchicalBalance;

class Account extends Model
{
    use HasFactory;
    use HasHierarchicalBalance;

    protected $fillable = [

        'parent_id',
        'account_type_id',
        'name',
        'number',
        'description',
    ];



    protected static function newFactory()
    {        return AccountFactory::new();
    }
}
