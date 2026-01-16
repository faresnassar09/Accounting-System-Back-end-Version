<?php

namespace Modules\Accounting\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Modules\Accounting\Database\Factories\JournalEntryFactory;

class JournalEntry extends Model
{
    use HasFactory;

    protected $fillable = [

        'user_id',
        'type',
        'total_credit',
        'total_debit',
        'reference',
        'description',
        'date',
    ];

    public function lines(){

        return $this->hasMany(JournalEntryLine::class);

    }

    public function scopeNormalJournalEntry(Builder $query){

      return  $query->where('type','journal');
    }

    public function scopeOpeningJournal(Builder $query){

        return $query->where('type','opening');
    }

    protected $casts = [
        'date' => 'datetime',
    ];

}
