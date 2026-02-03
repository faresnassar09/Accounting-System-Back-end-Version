<?php

namespace Modules\Accounting\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\DB;

use Modules\Accounting\Database\Factories\JournalEntryLineFactory;

class JournalEntryLine extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [

        'journal_entry_id',
        'account_id',
        'credit',
        'debit',
        'created_at'
    ];

    // public $timestamps = false;

    public function journalEntry(){

        return $this->belongsTo(JournalEntry::class);
    }
    
    public function scopeAccountEntryLines(Builder $query,$data){

        $accountId = $data->accountId;
        $startDate = $data->startDate;
        $endDate = $data->endDate;

        $query
        ->with('account')
        ->where('account_id',$accountId)
        ->when($startDate,fn($query) => $query->whereDate('created_at','>=',$startDate))
        ->when($endDate,fn($query) => $query->whereDate('created_at','<=',$endDate))
        ->select('journal_entry_lines.*',DB::raw('SUM(debit - credit)OVER (ORDER BY id) as running_balance'));

    }


    public function account(){

        return $this->belongsTo(Account::class);
    }



    protected function casts(){


        return [
             
        'created_at' => 'datetime',

        ];

    }


    public static function newFactory(){
         
        return JournalEntryLineFactory::new();
    }
    

}