<?php

namespace App\Providers;

use App\Helpers\Pusher;
//use App\Rules\NotBanned;
use Illuminate\Support\ServiceProvider;
use App\Strategies\Broadcasting\MessageBroadcastStrategy;
use App\Strategies\Broadcasting\PusherBroadcastStrategy;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(Pusher::class, function () {
            return new Pusher();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Register the custom validation rule
        \Illuminate\Support\Facades\Validator::extend('not_banned', NotBanned::class);

        $this->app->bind(
            MessageBroadcastStrategy::class,
            PusherBroadcastStrategy::class
        );
    }
}
