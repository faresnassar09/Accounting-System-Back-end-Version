<?php

namespace Modules\User\Listeners;

use Laravel\Passport\ClientRepository;

class CreatePersonalAuthenticationListener
{
    /**
     * Create the event listener.
     */
    public function __construct() {}

    /**
     * Handle the event.
     */
    public function handle($event): void
    {
        $existing = \DB::table('oauth_clients')
            ->where('name', 'users')
            ->first();

        if (! $existing) {
            $clients = app(ClientRepository::class);
            $client = $clients->createPersonalAccessGrantClient('users');
        }
    }
}
