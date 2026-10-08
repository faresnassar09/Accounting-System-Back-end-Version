<?php

namespace Modules\User\Policies;

use Modules\User\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Modules\Admin\Models\Admin;

class UserPolicy
{
    use HandlesAuthorization;

    /**
     * Create a new policy instance.
     */
    public function __construct() {}


}


