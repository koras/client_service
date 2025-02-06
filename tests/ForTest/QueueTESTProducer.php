<?php

namespace Tests\ForTest;

use App\Contracts\Models\OrderInterface;
use App\Contracts\Services\QueueProducerInterface;
use App\DTO\NotificationDto;

class QueueTESTProducer implements QueueProducerInterface
{

    public function publishGetCertificatesQueue(OrderInterface $order): void
    {
        return;
    }

    public function publishNotificationQueue(NotificationDto $notificationDto): void
    {
        return;
    }

    public function publishReorderQueue(OrderInterface $order): void
    {
        return;
    }

    public function publishOrderPaidQueue(OrderInterface $order): void
    {
        return;
    }
}