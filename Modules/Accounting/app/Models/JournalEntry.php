<?php

namespace Modules\Accounting\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\Accounting\Database\Factories\JournalEntryFactory;

class JournalEntry extends Model
{
    use HasFactory;

    protected $fillable = [


        'type',
        'total_credit',
        'total_debit',
        'reference',
        'description',
        'date',
        'status',
        'branch_id',
        'currency_code',
        'exchange_rate',
        'recurring_journal_entry_id',
    ];

    public function lines(){

        return $this->hasMany(JournalEntryLine::class);

    }

    public function branch()
    {
        return $this->belongsTo(\Modules\Branch\Models\Branch::class);
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class, 'currency_code', 'code');
    }

    public function recurringJournalEntry()
    {
        return $this->belongsTo(RecurringJournalEntry::class, 'recurring_journal_entry_id');
    }

    public function scopeNormalJournalEntry(Builder $query){

      return  $query->where('type','journal');
    }

    public function scopeOpeningJournal(Builder $query){

        return $query->where('type','opening');
    }

    protected $casts = [
        'date'          => 'datetime',
        'exchange_rate' => 'decimal:6',
    ];

    public static function newFactory(){

        return JournalEntryFactory::new();
    }

}
