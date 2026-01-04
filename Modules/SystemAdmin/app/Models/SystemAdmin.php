<?php

namespace Modules\SystemAdmin\Models;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Modules\SystemAdmin\Database\Factories\SystemAdminFactory;

class SystemAdmin extends Authenticatable
{
    use HasFactory;

    protected $fillable = ['name','email','password'];

}
