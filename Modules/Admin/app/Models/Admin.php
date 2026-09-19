<?php

namespace Modules\Admin\Models;

use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Modules\Admin\Database\Factories\AdminFactory;
use Modules\Branch\Models\Branch;
use Spatie\Permission\Traits\HasRoles;

class Admin extends Authenticatable implements FilamentUser
{
    use HasFactory,
        HasRoles;

    public function canAccessPanel(Panel $panel): bool
    {
        return true;
    }

    protected $fillable = [

        'name',
        'email',
        'password',  
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
