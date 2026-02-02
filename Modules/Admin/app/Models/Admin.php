<?php

namespace Modules\Admin\Models;


// use Filament\Models\Contracts\HasTenants;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;
use Modules\Admin\Database\Factories\AdminFactory;
use Modules\Branch\Models\Branch;
use Spatie\Permission\Traits\HasRoles;


class Admin extends Authenticatable
{
    // protected $connection = 'tenant';

    use HasFactory,
        HasApiTokens,
        HasRoles
        
        
        ;

    protected $fillable = [

        'name',
        'email',
        'password',  
        'organization_id',
        'branch_id',
        
    ];



    public function branch(){

        return $this->belongsTo(Branch::class);
    }

    public function scopeOnlyAdmins($query){

        $query->whereHas('roles',function($q){

            $q->where('name','admin');
        });

    }


    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }


    public static function newFactory()
    {
        return AdminFactory::new();
    }
}
