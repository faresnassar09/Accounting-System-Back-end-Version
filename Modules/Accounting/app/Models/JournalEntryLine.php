<?php

namespace Modules\Accounting\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

use Modules\Accounting\Database\Factories\JournalEntryLineFactory;

class JournalEntryLine extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [

        'source_type',
        'source_reference',
        'journal_entry_id',
        'account_id',
        'credit',
        'debit',
        'date'
    ];

    public function journalEntry()
    {

        return $this->belongsTo(JournalEntry::class);
    }

    public function account()
    {

        return $this->belongsTo(Account::class);
    }



    protected function casts()
    {


        return [

            'created_at' => 'datetime',

        ];
    }


    public static function newFactory()
    {

        return JournalEntryLineFactory::new();
    }
}
