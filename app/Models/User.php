<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Jetstream\HasProfilePhoto;
use Laravel\Sanctum\HasApiTokens;
use Modules\Branch\Models\Branch;
use Modules\Teams\Models\Team;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{


    use HasApiTokens;
    use HasFactory;
    use HasProfilePhoto;
    use Notifiable;
    use TwoFactorAuthenticatable;
    use SoftDeletes;
    use HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [

        'branch_id',
        'team_id',
        'name',
        'email',
        'password',
        'profile_photo_path',
    ];


    public function team()
    {

        return $this->belongsTo(Team::class);
    }

    public function branch(){

         return $this->belongsTo(Branch::class);
    }

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_recovery_codes',
        'two_factor_secret',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array<int, string>
     */
    protected $appends = [
        'roleNames',
        'permissionNames',
        'profile_photo_url',
    
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
     

    public function getRoleNameAttribute()
    {
       return $this->roles->first()->name;

    }

    public function getPermissionNamesAttribute(){

        return $this->getPermissionsViaRoles()->pluck('name');

    }

}