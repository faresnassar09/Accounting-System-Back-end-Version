<?php

namespace Modules\Accounting\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Branch\Models\Branch;

class RecurringJournalEntry extends Model
{
    use HasFactory;

    protected $fillable = [
        'reference_template',
        'description',
        'frequency',
        'start_date',
        'end_date',
        'last_run_date',
        'next_run_date',
        'status',
        'currency_code',
        'exchange_rate',
        'branch_id',
        'auto_post',
        'total_debit',
        'total_credit',
        'created_by',
    ];

    protected $casts = [
        'start_date'    => 'date',
        'end_date'      => 'date',
        'last_run_date' => 'date',
        'next_run_date' => 'date',
        'auto_post'     => 'boolean',
        'total_debit'   => 'decimal:2',
        'total_credit'  => 'decimal:2',
        'exchange_rate' => 'decimal:6',
    ];

    public function lines(): HasMany
    {
        return $this->hasMany(RecurringJournalEntryLine::class, 'recurring_journal_entry_id');
    }

    public function generatedEntries(): HasMany
    {
        return $this->hasMany(JournalEntry::class, 'recurring_journal_entry_id');
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class, 'currency_code', 'code');
    }

    public function scopeDue(Builder $query, $targetDate = null): Builder
    {
        $date = $targetDate ? Carbon::parse($targetDate)->toDateString() : now()->toDateString();

        return $query->where('status', 'active')
            ->whereDate('next_run_date', '<=', $date);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }
}
