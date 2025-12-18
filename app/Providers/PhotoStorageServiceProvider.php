<?php

namespace App\Providers;

use App\Strategies\PhotoStorageStrategy\PhotoStorageStrategy;
use App\Strategies\PhotoStorageStrategy\LocalStorageStrategy;
use App\Strategies\PhotoStorageStrategy\CloudinaryStorageStrategy;
use Illuminate\Support\ServiceProvider;

class PhotoStorageServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(PhotoStorageStrategy::class, function ($app) {
            return new CloudinaryStorageStrategy();
        });
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        // You can add additional bootstrapping logic if needed.
    }
}
