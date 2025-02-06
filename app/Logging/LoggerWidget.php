<?php

namespace App\Logging;

use Monolog\Logger;

class LoggerWidget
{
    public function __invoke(array $config): Logger
    {
        $logger = new Logger('rabbitmq');
        $logger->pushHandler(new LoggerWidgetHandler());

        return $logger;
    }
}