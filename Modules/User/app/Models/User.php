<?php

namespace Modules\User\Models;

use App\Models\User as OriginalUsreModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Permission\Traits\HasRoles;
// use Modules\User\Database\Factories\UserModuleFactory;

class User extends OriginalUsreModel
{
    use HasFactory;
    use HasRoles;

    protected $guard_name = 'web'; 


}