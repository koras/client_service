<?php

namespace App\Contracts\Services;

use App\Contracts\Consumers\BaseRabbitConsumerInterface;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AMQPStreamConnection;

interface RabbitMQInterface
{
    public function init(string $queueName = ''): self;
    public function getQueueName();
    public function setQueue(string $queueName): self;
    public function subscribe(BaseRabbitConsumerInterface $consumer): void;
    public function sendMessage(string $msg): void;

    public function getConnection(): AMQPStreamConnection;

    public function getChannel(): AMQPChannel;
}