<?php

namespace Modules\Accounting\Observers;

use Modules\Accounting\Models\JournalEntryLine;

class JournalEntryLineObserver
{

    public function created(JournalEntryLine $journalentryline): void
    {


        $account = $journalentryline->account;
        $debit = $journalentryline->debit;
        $credit = $journalentryline->credit;

        if (!$account->accountType) {

            return;
        }

        $accountGroup = $account?->accountType?->account_group;



        $isDebitNormal = in_array($accountGroup, ['assets', 'expenses']);
        $net = $isDebitNormal ? ($debit - $credit) : ($credit - $debit);

        $account->increment('calculated_balance', $net);
    }


    public function updated(JournalEntryLine $journalentryline): void {}


    public function deleted(JournalEntryLine $journalentryline): void {}



    public function restored(JournalEntryLine $journalentryline): void {}
}
