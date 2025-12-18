<?php

namespace App\Providers;

use App\Strategies\AuthenticationStrategy\AuthStrategy;
use App\Strategies\AuthenticationStrategy\SocialAuthStrategy;
use App\Strategies\AuthenticationStrategy\StandardAuthStrategy;
use Illuminate\Support\ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->bind(AuthStrategy::class, function ($app) {
            // Determine which strategy to use, for example based on request data
            if (request()->has('provider')) {
                return new SocialAuthStrategy();
            }

            return new StandardAuthStrategy();
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
