<?php

namespace App\Contracts\Consumers;

use PhpAmqpLib\Message\AMQPMessage;

interface BaseRabbitConsumerInterface
{
    public function consume(AMQPMessage $message): void;
}