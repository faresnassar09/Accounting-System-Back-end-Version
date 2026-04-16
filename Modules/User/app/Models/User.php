<?php

namespace Modules\User\Models;

use App\Models\User as OriginalUsreModel;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;
use Modules\Branch\Models\Branch;
use Modules\Teams\Models\Team;
use Modules\User\database\factories\UserFactory;
use Spatie\Permission\Traits\HasRoles;

class User extends OriginalUsreModel
{
    use HasFactory,
     HasRoles,
     HasApiTokens,
     Notifiable,
     SoftDeletes,
     HasRoles;

    protected static function newFactory()
    {        return UserFactory::new();
    }

    public function scopeWithoutExtrnalUser(Builder $query){

        $query->where('email','!=','external@externalservice.com');

    }



    
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

    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array<int, string>
     */
    protected $appends = [
        'roleName',
        'permissionNames',
    
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
       return $this->roles?->first()?->name;

    }

    public function getPermissionNamesAttribute(){

        return $this->getPermissionsViaRoles()->pluck('name');

    }


}