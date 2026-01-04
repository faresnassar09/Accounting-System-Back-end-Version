<?php

namespace Modules\Admin\Observers;

use Modules\Admin\Models\Admin;

class AdminObserver
{

    public function created(Admin $admin): void {}

    public function updated(Admin $admin): void {}


    public function deleted(Admin $admin): void {}


    public function creating(Admin $admin){




    }
}
