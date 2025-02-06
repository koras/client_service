<?php

namespace App\Logging\Contracts;

/**
 * Интерфейс для отправки сообщений в RabbitMQ
 */
interface RabbitMQLogConnectorInterface
{
    /**
     * Инициировать очередь в канал
     *
     * @param string $queueName
     * @return RabbitMQLogConnectorInterface
     */
    public function initQueue(string $queueName = ''): self;

    /**
     * Отправить сообщение в очередь
     *
     * @param string $msg
     * @return void
     */
    public function sendMessage(string $msg): void;

}
