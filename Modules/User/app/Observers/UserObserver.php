<?php

namespace Modules\User\Observers;

use App\Enums\EloquentEvents;
use App\Models\User ;
use App\Services\Files\FileService;
use App\Services\Logging\ActivityService;

class UserObserver
{

    public function __construct(
        
        public FileService $fileService,
        public ActivityService $activityService,

        ){}

    public function created(User $user): void
    {     

    $this->activityService->log(

        effected: $user,
        event:    EloquentEvents::CREATED,
        message:  EloquentEvents::CREATED->label(),
        data:     [],
    );
    }

    /**
     * Handle the User "updated" event.
     */
    public function updated(User $user): void
    {


        if ($user->isDirty()) {
             
            $this->activityService->log(

                effected: $user,
                event:    EloquentEvents::UPDATED,
                message:  EloquentEvents::UPDATED->label(),
                data:     [],
            );

        }

    }

    public function deleted(User $user): void
    {

        if ($user->profile_photo_path) {
             
             $this->fileService->deleteFile('local',$user->profile_photo_path);

        }
  
        $this->activityService->log(
            
            effected: $user,
            event:    EloquentEvents::DELETED,
            message:  EloquentEvents::DELETED->label(),
            data:     [],
        );

    }

    public function creating(User $user){

        $user->branch_id  = $user->branch_id ?? current_guard_user()->branch_id ?? null;
        
    }    
}
