<?php

namespace Modules\Accounting\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
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



}
