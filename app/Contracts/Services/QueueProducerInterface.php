<?php

namespace App\Contracts\Services;

use App\Contracts\Models\OrderInterface;
use App\DTO\NotificationDto;

interface QueueProducerInterface
{
    public function publishGetCertificatesQueue(OrderInterface $order): void;

    public function publishOrderPaidQueue(OrderInterface $order): void;

    public function publishReorderQueue(OrderInterface $order): void;

    public function publishNotificationQueue(NotificationDto $notificationDto): void;

}

