<?php

namespace Modules\Teams\Observers;

use App\Enums\EloquentEvents;
use App\Services\Logging\ActivityService;
use Modules\Teams\Models\Team;

class TeamObserver
{

    public function __construct(public ActivityService $activityService){}

    public function created(Team $team): void
    {
    
        $this->activityService->log(
            
            effected: $team,
            event:    EloquentEvents::DELETED,
            message:  EloquentEvents::DELETED->label(),
            data:     [],
        );
        
    }

    /**
     */
    public function updated(Team $team): void
    {


        if ($team->isDirty()) {
              
            $this->activityService->log(
            
                effected: $team,
                event:    EloquentEvents::UPDATED,
                message:  EloquentEvents::UPDATED->label(),
                data:     [],
            );
            
        }

    }




    public function deleted(Team $team): void
    {
        $this->activityService->log(
            
            effected: $team,
            event:    EloquentEvents::DELETED,
            message:  EloquentEvents::DELETED->label(),
            data:     [],
        );
    }


    // public function creating(Team $Team){

    //     $Team->organization_id = current_guard_user()->organization_id;
        
    // }
      
}  
