<?php

namespace App\Providers;

use App\Logging\Contracts\LogSenderInterface;
use App\Logging\LoggerWidgetHandler;
use App\Logging\LogRabbitMQSender;
use Illuminate\Support\ServiceProvider;

class LoggerServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(LogSenderInterface::class, LogRabbitMQSender::class);
        $this->app->singleton(LoggerWidgetHandler::class, function ($app) {
            return new LoggerWidgetHandler();
        });
    }
}