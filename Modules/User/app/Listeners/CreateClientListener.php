<?php

namespace Modules\User\Listeners;

use Laravel\Passport\ClientRepository;

class CreateClientListener
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
            ->where('name', 'main')
            ->first();

        if (! $existing) {
            $clients = app(ClientRepository::class);
            $client = $clients->createClientCredentialsGrantClient('main');
            \Log::info('OAuth Client created', ['client_id' => $client->id]);
        }
    }
}
