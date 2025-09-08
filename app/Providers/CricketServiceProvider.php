<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\CricketApiService;

class CricketServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton(CricketApiService::class, function ($app) {
            return new CricketApiService();
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




