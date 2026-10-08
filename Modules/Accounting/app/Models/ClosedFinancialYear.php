<?php

namespace Modules\Accounting\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Modules\Accounting\Database\Factories\ClosedFinancialYearFactory;

class ClosedFinancialYear extends Model
{
    use HasFactory;

    protected $fillable = [
        
        'closed_by',
        'year',
        'net_profit_loss',
        'retained_earnings_account_id',
    ];
    
}
