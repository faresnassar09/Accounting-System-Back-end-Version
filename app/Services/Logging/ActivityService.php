<?php

namespace App\Services\Logging;

use App\Enums\EloquentEvents;

 class ActivityService {


    public function log($effected, EloquentEvents $event,$message,array $data = [] ){

         
        activity()
        ->causedBy(current_guard_user())
        ->performedOn($effected)
        ->event($event->value)
        ->withProperties($data)
        ->log($message);

    }

    
 }

