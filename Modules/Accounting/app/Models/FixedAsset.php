<?php

namespace Modules\Accounting\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Branch\Models\Branch;

class FixedAsset extends Model
{
    use HasFactory;

    protected $fillable = [
        'asset_number',
        'name',
        'category',
        'purchase_date',
        'purchase_cost',
        'salvage_value',
        'useful_life_months',
        'depreciation_method',
        'asset_account_id',
        'depreciation_expense_account_id',
        'accumulated_depreciation_account_id',
        'accumulated_depreciation',
        'book_value',
        'status',
        'disposal_date',
        'disposal_amount',
        'branch_id',
    ];

    protected $casts = [
        'purchase_date'            => 'date',
        'disposal_date'            => 'date',
        'purchase_cost'            => 'decimal:2',
        'salvage_value'            => 'decimal:2',
        'useful_life_months'       => 'integer',
        'accumulated_depreciation' => 'decimal:2',
        'book_value'               => 'decimal:2',
        'disposal_amount'          => 'decimal:2',
    ];

    public function assetAccount(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'asset_account_id');
    }

    public function depreciationExpenseAccount(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'depreciation_expense_account_id');
    }

    public function accumulatedDepreciationAccount(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'accumulated_depreciation_account_id');
    }

    public function schedules(): HasMany
    {
        return $this->hasMany(DepreciationSchedule::class, 'fixed_asset_id')->orderBy('period_date', 'asc');
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }
}
