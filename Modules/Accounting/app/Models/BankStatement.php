<?php

namespace Modules\Accounting\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BankStatement extends Model
{
    use HasFactory;

    protected $fillable = [
        'account_id',
        'statement_date',
        'filename',
        'opening_balance',
        'closing_balance',
        'reconciled_balance',
        'status',
    ];

    protected $casts = [
        'statement_date'     => 'date',
        'opening_balance'    => 'decimal:2',
        'closing_balance'    => 'decimal:2',
        'reconciled_balance' => 'decimal:2',
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'account_id');
    }

    public function lines(): HasMany
    {
        return $this->hasMany(BankStatementLine::class, 'bank_statement_id');
    }

    public function getCalculatedBalanceAttribute(): float
    {
        $netMatched = (float) $this->lines()->where('status', 'matched')->sum('amount');

        return round((float) $this->opening_balance + $netMatched, 2);
    }

    public function getDiscrepancyAttribute(): float
    {
        return round((float) $this->closing_balance - $this->calculated_balance, 2);
    }
}
