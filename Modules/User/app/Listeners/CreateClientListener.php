<?php

namespace Modules\User\Listeners;

use Laravel\Passport\ClientRepository;
use Log;

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


        $clients = app(ClientRepository::class);

        $client = $clients->createClientCredentialsGrantClient(
            'main',
        );

        Log::info('secret',[$client->id,$client->plainSecret]);

    }
}
