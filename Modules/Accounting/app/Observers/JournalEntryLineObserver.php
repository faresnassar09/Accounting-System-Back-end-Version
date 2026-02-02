<?php

namespace Modules\Accounting\Observers;

use Illuminate\Container\Attributes\Log;
use Modules\Accounting\Models\JournalEntryLine;

class JournalEntryLineObserver
{

    public function created(JournalEntryLine $journalentryline): void {

            $amount = $journalentryline->debit > 0 
            ? $journalentryline->debit 
            : $journalentryline->credit;

        $account = $journalentryline->account;
        $account->incrementQuietly('calculated_balance',$amount);    
        
    }


    public function updated(JournalEntryLine $journalentryline): void {}


    public function deleted(JournalEntryLine $journalentryline): void {}

    
 
    public function restored(JournalEntryLine $journalentryline): void {}


}
