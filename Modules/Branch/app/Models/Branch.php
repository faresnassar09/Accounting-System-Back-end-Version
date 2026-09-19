<?php

namespace Modules\Branch\Models;

use Modules\User\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Admin\Models\Admin;



class Branch extends Model
{
    use HasFactory;

    protected $fillable = [

        'name',
        'phone',
        'address',
        'code',
        'active',
    ];

    public function users(){

        return $this->hasMany(User::class);
        
    }

    public function admins(){

        return $this->hasMany(Admin::class)->whereHas('roles',function($query){
            
            $query->where('name','admin');
            

        });
        
    }   

}
