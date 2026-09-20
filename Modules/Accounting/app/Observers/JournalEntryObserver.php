<?php

namespace Modules\Accounting\Observers;

use Modules\Accounting\Models\JournalEntry;
use App\Services\Logging\ActivityService;
use App\Enums\EloquentEvents;


class JournalEntryObserver
{
 
    public function __construct(public ActivityService $activityService){}

    public function created(JournalEntry $JournalEntry): void {

        $this->activityService->log(

           effected: $JournalEntry,
           event: EloquentEvents::CREATED,
           message: EloquentEvents::CREATED->label(),
           data:  ["Entry Reference :: $JournalEntry->reference"]

        );

        app(\Modules\Accounting\Services\Logging\AuditLogService::class)->record(
            event: 'created',
            model: $JournalEntry,
            description: "Created Journal Entry #{$JournalEntry->reference} (Debit: \${$JournalEntry->total_debit})",
            newValues: $JournalEntry->only(['reference', 'total_debit', 'total_credit', 'status', 'type', 'date'])
        );
    }

    /**
     * Handle the Journal "updated" event.
     */
    public function updated(JournalEntry $JournalEntry): void {

        $this->activityService->log(

            effected: $JournalEntry,
            event: EloquentEvents::UPDATED,
            message: EloquentEvents::UPDATED->label(),
            data:  ["Entry Reference :: $JournalEntry->reference"]
 
         );

        $event = ($JournalEntry->status === 'cancled') ? 'reversed' : 'updated';
        app(\Modules\Accounting\Services\Logging\AuditLogService::class)->record(
            event: $event,
            model: $JournalEntry,
            description: ucfirst($event) . " Journal Entry #{$JournalEntry->reference}",
            oldValues: $JournalEntry->getOriginal(),
            newValues: $JournalEntry->getChanges()
        );
    }

    public function deleted(JournalEntry $JournalEntry): void {

        $this->activityService->log(

            effected: $JournalEntry,
            event: EloquentEvents::DELETED,
            message: EloquentEvents::DELETED->label(),
            data:  ["Entry Reference :: $JournalEntry->reference"]
 
         );

        app(\Modules\Accounting\Services\Logging\AuditLogService::class)->record(
            event: 'deleted',
            model: $JournalEntry,
            description: "Deleted Journal Entry #{$JournalEntry->reference}",
            oldValues: $JournalEntry->toArray()
        );
    }

}
