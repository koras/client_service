<?php

namespace App\Logging;

use App\Logging\Contracts\RabbitMQLogConnectorInterface;
use Error;
use Exception;
use PhpAmqpLib\Channel\AbstractChannel;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
use PhpAmqpLib\Wire\AMQPTable;


/**
 * Класс для отправки сообщений в RabbitMQ
 */
class RabbitMQLogConnector implements RabbitMQLogConnectorInterface
{
    private AMQPStreamConnection $connection;
    private AbstractChannel|AMQPChannel $channel;
    private string $queueName;

    public function __construct()
    {
        try {
            $this->connection = new AMQPStreamConnection(
                    config('logger-rabbit.connection.host'),
                    config('logger-rabbit.connection.port'),
                    config('logger-rabbit.connection.login'),
                    config('logger-rabbit.connection.password'),
                    config('logger-rabbit.connection.vhost')
                );

            $this->channel = $this->connection->channel();
        } catch (Exception|Error $e) {

        }

    }

    /**
     * Инициировать очередь в канал
     *
     * @param string $queueName
     * @return RabbitMQLogConnectorInterface
     */
    public function initQueue(string $queueName = ''): RabbitMQLogConnectorInterface
    {
        $options = new AMQPTable([
            'x-max-priority' => 3,
        ]);

        if ($queueName == '') {
            $queueName = config('logger-rabbit.queueName');
        }
        $this->queueName = $queueName;

        try {
            $this->channel->queue_declare($this->queueName, false, true, false, false, $options);
        } catch (Exception|Error $e) {

        }

        return $this;
    }

    /**
     * Отправить сообщение в очередь
     *
     * @param string $msg
     * @return void
     */
    public function sendMessage(string $msg): void
    {
        $this->publish(self::createMessage($msg));
    }

    private static function createMessage(string $msg): AMQPMessage
    {
        return new AMQPMessage($msg);
    }

    private function publish(AMQPMessage $msg): void
    {
        try {
            $this->channel->basic_publish($msg, '', $this->queueName);

        } catch (Exception|Error $e) {

        }
    }

    /**
     * @throws Exception
     */
    public function __destruct()
    {
        if (!empty($this->channel)) {
            $this->channel->close();

        }

        if (!empty($this->connection)) {
            $this->connection->close();

        }
    }

}
