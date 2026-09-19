<?php

namespace Modules\User\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Hash;
use Modules\User\Models\User;
use Str;

class CreateUserListener
{
    /**
     * Create the event listener.
     */
    public function __construct() {}

    /**
     * Handle the event.
     */
    public function handle($event): void {
        User::firstOrCreate(
            ['email' => 'external@externalservice.com'],
            [
                'name' => 'external_service',
                'password' => Hash::make(Str::uuid()),
            ]
        );
    }
}
