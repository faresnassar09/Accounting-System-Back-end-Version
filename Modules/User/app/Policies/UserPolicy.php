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


public function update(Admin $admin, User $user)
{
    return $admin->company_id === $user->company_id;
}

public function view(Admin $admin, User $user)
{
    return $admin->company_id === $user->company_id;
}

public function delete(Admin $admin, User $user)
{
    return $admin->company_id === $user->company_id;
}
}


