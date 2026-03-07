<?php

namespace Modules\User\Models;

use App\Models\User as OriginalUsreModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Laravel\Passport\HasApiTokens;
use Modules\User\database\factories\UserFactory;
use Spatie\Permission\Traits\HasRoles;

class User extends OriginalUsreModel
{
    use HasFactory,
     HasRoles,
     HasApiTokens;


    protected static function newFactory()
    {        return UserFactory::new();
    }
}