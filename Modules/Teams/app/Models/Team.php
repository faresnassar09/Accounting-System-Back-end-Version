<?php

namespace Modules\Teams\Models;

use Modules\User\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\Admin\Models\Admin;
use Modules\Company\Models\CompanyScope;
// use Modules\Teams\Database\Factories\TeamFactory;

class Team extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [

        'name',

    ];


    public function users(){


        return $this->hasMany(User::class);
    }

    public function admin(){


        return $this->belongsTo(Admin::class);
    }


}
