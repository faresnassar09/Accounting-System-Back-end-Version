<?php

namespace Modules\Accounting\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DepreciationSchedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'fixed_asset_id',
        'period_date',
        'depreciation_amount',
        'accumulated_after',
        'book_value_after',
        'journal_entry_id',
    ];

    protected $casts = [
        'period_date'         => 'date',
        'depreciation_amount' => 'decimal:2',
        'accumulated_after'   => 'decimal:2',
        'book_value_after'    => 'decimal:2',
    ];

    public function asset(): BelongsTo
    {
        return $this->belongsTo(FixedAsset::class, 'fixed_asset_id');
    }

    public function journalEntry(): BelongsTo
    {
        return $this->belongsTo(JournalEntry::class, 'journal_entry_id');
    }
}
