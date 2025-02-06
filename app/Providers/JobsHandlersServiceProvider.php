<?php

namespace App\Providers;

use App\Contracts\JobsHandlers\PaidOrderTransferToVendorHandlerInterface;
use App\JobsHandlers\PaidOrderTransferToVendorHandler;
use Illuminate\Support\ServiceProvider;

class JobsHandlersServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(PaidOrderTransferToVendorHandlerInterface::class, PaidOrderTransferToVendorHandler::class);
    }

    public function boot(): void
    {
        //
    }
}