<?php

namespace Modules\Accounting\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Modules\Accounting\Database\Factories\AccountTypeFactory;

class AccountType extends Model
{
    use HasFactory;

    protected $fillable = [
        
        'type',
        'account_group',
    ];

}
