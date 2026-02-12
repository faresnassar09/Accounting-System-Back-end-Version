<?php

namespace Modules\Branch\Observers;

use Modules\Branch\Models\Branch;

class BranchObserver
{
    /**
     * Handle the Branch "created" event.
     */
    public function created(Branch $branch): void {}

    /**
     * Handle the Branch "updated" event.
     */
    public function updated(Branch $branch): void {}

    /**
     * Handle the Branch "deleted" event.
     */
    public function deleted(Branch $branch): void {}

    public function creating(Branch $branch){    }
}
