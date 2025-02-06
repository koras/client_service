<?php

namespace App\Services;

use App\Contracts\Models\OrderInterface;
use App\Contracts\Services\QueueProducerInterface;
use App\Contracts\Services\RabbitMQInterface;
use App\DTO\NotificationDto;
use App\Jobs\PaidOrderTransferToVendor;
use App\Contracts\Services\LifeCycleServiceInterface;
use App\Jobs\ProbeQueue;
use App\Logging\WidgetLogObject;
use Illuminate\Support\Facades\Log;

readonly class QueueProducer implements QueueProducerInterface
{
    public function __construct(
        private RabbitMQInterface $rabbitMQ,
        private LifeCycleServiceInterface $lifeCycle
    )
    {
    }

    public function publishGetCertificatesQueue(OrderInterface $order): void
    {
        $queueName = config('queue-names.getCertificates');
        $message = json_encode(['orderId' => $order->id]);
        $this->rabbitMQ
            ->init($queueName)
            ->sendMessage($message);

        $this->lifeCycle->createStatus($order->id,"samara",10);


        $log = WidgetLogObject::make('publish Queue ' . $queueName . ' for order:' . $order->id, 'QueueProducer', $order);
        Log::info($log->message, $log->toContext());
    }

    /**
     * Отправляем сообщение в очередь обработки оплаченных заказов
     *
     * @param OrderInterface $order
     * @return void
     */
    public function publishOrderPaidQueue(OrderInterface $order): void
    {
        $queueName = config('queue-names.orderPaid');
        PaidOrderTransferToVendor::dispatch($order->id)->onQueue($queueName);
        $this->lifeCycle->createStatus($order->id,"samara",11);

        $log = WidgetLogObject::make('publish Queue ' . $queueName . ' for order:' . $order->id, 'QueueProducer', $order);
        Log::info($log->message, $log->toContext());
    }

    /**
     * @param OrderInterface $order
     * @return void
     */
    public function publishReorderQueue(OrderInterface $order): void
    {
        $queueName = config('queue-names.reorder');
        PaidOrderTransferToVendor::dispatch($order->id)->onQueue($queueName);
        $this->lifeCycle->createStatus($order->id,"samara",12);

        $log = WidgetLogObject::make('publish Queue ' . $queueName . ' for order:' . $order->id, 'QueueProducer', $order);
        Log::info($log->message, $log->toContext());
    }

    /**
     * @param NotificationDto $notificationDto
     * @return void
     */
    public function publishNotificationQueue(NotificationDto $notificationDto): void
    {
        $queueName = config('queue-names.notification');
        $message = json_encode($notificationDto->toArray());
        $this->rabbitMQ
            ->init($queueName)
            ->sendMessage($message);

        $log = WidgetLogObject::make('publish Queue ' . $queueName . ' for widget:' . $notificationDto->data['widgetId'], 'QueueProducer');
        Log::info($log->message, $log->toContext());
    }

    public function publishProbeQueue($message, int $delay = 0)
    {
        $queueName = config('queue-names.probe');
        ProbeQueue::dispatch($message)->onQueue($queueName)->delay($delay);

        $log = WidgetLogObject::make('publish Queue ' . $queueName, 'QueueProducer');
        Log::info($log->message, $log->toContext());

    }

}
