<?php

namespace App\Logging;

use App\Logging\Contracts\LogSenderInterface;
use App\Logging\Contracts\RabbitMQLogConnectorInterface;

class LogRabbitMQSender implements LogSenderInterface
{
    private string $queueName;
    public function __construct(
        private readonly RabbitMQLogConnectorInterface $rabbitMQ
    )
    {
        $this->queueName = config('logger-rabbit.queueName');
    }

    public function send(string $message): void
    {
        $this->rabbitMQ
            ->initQueue($this->queueName)
            ->sendMessage($message);
    }
}