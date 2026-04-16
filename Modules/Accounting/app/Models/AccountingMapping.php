<?php

namespace Modules\Accounting\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Modules\Accounting\Database\Factories\AccountingMappingFactory;

class AccountingMapping extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [ 

        'account_id',
        'integration_key',
        'description',
        'label',
    ];

    public function account(){


        return $this->belongsTo(Account::class);
    }

    // protected static function newFactory(): AccountingMappingFactory
    // {
    //     // return AccountingMappingFactory::new();
    // }
}
