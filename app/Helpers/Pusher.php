<?php

namespace App\Helpers;

use GuzzleHttp\Client;
use GuzzleHttp\Promise\PromiseInterface;
use Pusher\Pusher as PusherClient;
use Pusher\PusherException;

class Pusher
{
    protected PusherClient $pusher;

    /**
     * @throws PusherException
     */
    public function __construct()
    {
        $options = [
            'cluster' => env('PUSHER_APP_CLUSTER', 'eu'),
            'useTLS' => env('PUSHER_USE_TLS', false),
        ];

        $customClient = new Client();

        $this->pusher = new PusherClient(
            env('PUSHER_APP_KEY', '26143f87a08bdfeab780'),
            env('PUSHER_APP_SECRET', 'bc9f0158cdd1ebd43f76'),
            env('PUSHER_APP_ID', '1843953'),
            $options,
            $customClient
        );
    }

    /**
     * Trigger a Pusher event asynchronously.
     *
     * @param array|string $channels
     * @param string $event
     * @param array $data
     * @return PromiseInterface
     */
    public function triggerAsync(array|string $channels, string $event, array $data): void
    {
        $promise = $this->pusher->triggerAsync($channels, $event, $data);

        $promise->then(function ($result) {
            return $result;
        });

        $promise->wait();
    }

    /**
     * Trigger a Pusher event synchronously.
     *
     * @param array|string $channels
     * @param string $event
     * @param array $data
     * @return object
     */
    public function trigger(array|string $channels, string $event, array $data): object
    {
        return $this->pusher->trigger($channels, $event, $data);
    }
}