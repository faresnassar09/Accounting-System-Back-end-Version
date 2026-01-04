<?php

namespace Modules\Authorization\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Permission\Models\Permission as OriginalModel;
class Permission extends OriginalModel
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [];

    // protected static function newFactory(): PermissionFactory
    // {
    //     // return PermissionFactory::new();
    // }
}
