<?php

namespace Modules\Accounting\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Modules\Accounting\Database\Factories\JournalEntryFactory;

class JournalEntry extends Model
{
    use HasFactory;

    protected $fillable = [

        'user_id',
        'total_credit',
        'total_debit',
        'reference',
        'description',
        'date',
    ];

    public function lines(){

        return $this->hasMany(JournalEntryLine::class);

    }

    protected $casts = [
        'date' => 'datetime',
    ];

}
