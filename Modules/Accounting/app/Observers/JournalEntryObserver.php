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
    }



    public function deleted(JournalEntry $JournalEntry): void {


        $this->activityService->log(

            effected: $JournalEntry,
            event: EloquentEvents::DELETED,
            message: EloquentEvents::DELETED->label(),
            data:  ["Entry Reference :: $JournalEntry->reference"]
 
         );
    }

}
