<?php

namespace Modules\Accounting\Policies;

use Illuminate\Auth\Access\HandlesAuthorization;

class JournalEntryPolicy
{
    use HandlesAuthorization;

    public function __construct() {}
}
