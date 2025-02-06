<?php

namespace App\Services;

use App\Contracts\Consumers\BaseRabbitConsumerInterface;
use App\Contracts\Services\RabbitMQInterface;
use App\Logging\WidgetLogObject;
use Exception;
use Illuminate\Support\Facades\Log;
use PhpAmqpLib\Channel\AbstractChannel;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
use PhpAmqpLib\Wire\AMQPTable;

class RabbitMQ implements RabbitMQInterface
{
    private AMQPStreamConnection $connection;
    private AbstractChannel|AMQPChannel $channel;
    private string $queueName;

    /**
     * @param string $queueName
     * @return $this
     * @throws Exception
     */
    public function init(string $queueName = ''): self
    {
        $options = new AMQPTable([
            'x-max-priority' => 3,
        ]);

        if ($queueName == '') {
            $queueName = config('queue.connections.rabbitmq.queue');
        }
        $this->queueName = $queueName;

        try {
            $this->connection = new AMQPStreamConnection(
                config('queue.connections.rabbitmq.host'),
                config('queue.connections.rabbitmq.port'),
                config('queue.connections.rabbitmq.login'),
                config('queue.connections.rabbitmq.password'),
                config('queue.connections.rabbitmq.vhost')
            );
            $this->channel = $this->connection->channel();

            $this->channel->queue_declare($this->queueName, false, true, false, false, $options);
        } catch (Exception|\Error $e) {
            $log = WidgetLogObject::make('ERROR RABBIT init. Error: ' . $e->getMessage(), 'RabbitMQ');
            Log::critical($log->message, $log->toContext());
            throw new Exception('ERROR RABBIT init. Error: ' . $e->getMessage());
        }

        return $this;
    }

    public function getQueueName(): string
    {
        return $this->queueName;
    }

    public function setQueue(string $queueName): self
    {
        // Отмена подписки на предыдущую очередь
        $this->channel->basic_cancel($this->queueName);

        // Удаление предыдущей очереди
        $this->channel->queue_delete($this->queueName);

        // Создание новой очереди с новым именем
        $this->channel->queue_declare($queueName, false, true, false, false);

        // Установка нового имени очереди в свойстве
        $this->queueName = $queueName;

        return $this;
    }

    public function subscribe(BaseRabbitConsumerInterface $consumer): void
    {
        $callback = function ($msg) use ($consumer) {
            try {
                $consumer->consume($msg);
                $this->channel->basic_ack($msg->delivery_info['delivery_tag']); // Подтверждение успешной обработки
            } catch (Exception $e) {
                // Обработка ошибки при неудачной обработке сообщения
                $this->channel->basic_nack($msg->delivery_info['delivery_tag'], false, true);
            }
        };

        $this->channel->basic_qos(0, 1, false);
        $this->channel->basic_consume($this->queueName, '', false, false, false, false, $callback);

        while (count($this->channel->callbacks)) {
            $this->channel->wait();
        }
    }

    public function sendMessage(string $msg): void
    {
        $this->publish(self::createMessage($msg));
    }

    public function getConnection(): AMQPStreamConnection
    {
        return $this->connection;
    }

    public function getChannel(): AMQPChannel
    {
        return $this->channel;
    }

    private static function createMessage(string $msg): AMQPMessage
    {
        return new AMQPMessage($msg);
    }

    /**
     * @param AMQPMessage $msg
     * @return void
     * @throws Exception
     */
    private function publish(AMQPMessage $msg): void
    {
        try {
            $this->channel->basic_publish($msg, '', $this->queueName);

        } catch (Exception|\Error $e) {
            $log = WidgetLogObject::make('ERROR RABBIT publish. Error: ' . $e->getMessage(), 'RabbitMQ');
            Log::critical($log->message, $log->toContext());
            throw new Exception('ERROR RABBIT init. Error: ' . $e->getMessage());
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