<?php

namespace App\Models;

use Spatie\Activitylog\Models\Activity as OriginalActivityModel;

class Activity extends OriginalActivityModel
{



    public static function booted(){

        static::creating(function ($model) {
            if (current_guard_user()) {
                $user = current_guard_user();

                $model->branch_id = $user->branch_id ?? null ;

            }
        });


    }
}
