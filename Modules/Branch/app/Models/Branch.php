<?php

namespace Modules\Branch\Models;

use Modules\User\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Admin\Models\Admin;
use Modules\Company\Models\CompanyScope;
use Modules\Organization\Models\Organization;

// use Modules\Branch\Database\Factories\BranchFactory;
// #[ScopedBy(CompanyScope::class)]

class Branch extends Model
{
    use HasFactory;

    protected $fillable = [

        'organization_id',
        'name',
        'phone',
        'address',
        'code',
    ];

    public function users(){

        return $this->hasMany(User::class);
        
    }

    public function admins(){

        return $this->hasMany(Admin::class)->role('admin');
        
    }   

    public function organization(){

        return $this->belongsTo(Organization::class);
    }



}
