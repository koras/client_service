<?php

namespace App\Providers;

use App\Contracts\Services\ErrorServiceInterface;
use App\Services\ErrorService;
use Illuminate\Support\ServiceProvider;

class ErrorServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(ErrorServiceInterface::class, ErrorService::class);
        $this->app->singleton(ErrorService::class, ErrorService::class);
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}