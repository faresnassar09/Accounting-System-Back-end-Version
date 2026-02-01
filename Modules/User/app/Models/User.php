<?php

namespace Modules\User\Models;

use App\Models\User as OriginalUsreModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Permission\Traits\HasRoles;
use Modules\User\database\factories\UserFactory;

class User extends OriginalUsreModel
{
    use HasFactory;
    use HasRoles;

    protected static function newFactory()
    {        return UserFactory::new();
    }
}