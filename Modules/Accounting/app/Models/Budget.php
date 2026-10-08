<?php

namespace Modules\Accounting\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Branch\Models\Branch;

class Budget extends Model
{
    use HasFactory;

    protected $fillable = [
        'fiscal_year',
        'account_id',
        'branch_id',
        'allocated_amount',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'fiscal_year'      => 'integer',
        'allocated_amount' => 'decimal:2',
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function scopeForYear($query, int $year)
    {
        return $query->where('fiscal_year', $year);
    }

    public function scopeForBranch($query, ?int $branchId)
    {
        return $query->when($branchId, fn ($q) => $q->where('branch_id', $branchId));
    }
}
