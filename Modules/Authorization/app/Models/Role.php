<?php

namespace Modules\Authorization\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Permission\Models\Role as BaseModel;

class Role extends BaseModel
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [];

    // protected static function newFactory(): RoleFactory
    // {
    //     // return RoleFactory::new();
    // }
}
