<?php

return [
    'queueName' => 'logs_service',
    'nameProject' => 'widget',
    'nameServer' => env('NAME_SERVER', ''),
    'timeStorage' => [
        'short' => 'short',
        'long' => 'long',
    ],
    'connection' => [
        'host' => env('RABBITMQ_LOG_HOST', ''),
        'port' => env('RABBITMQ_LOG_PORT', ''),
        'vhost' => env('RABBITMQ_LOG_VHOST', ''),
        'login' => env('RABBITMQ_LOG_USER', ''),
        'password' => env('RABBITMQ_LOG_PASSWORD', ''),
    ]
];