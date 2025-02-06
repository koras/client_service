<?php

namespace App\Contracts\Services\Bitrix24;

use App\Contracts\Models\WidgetInterface;
use App\DTO\NotificationDto;

interface BitrixTaskServiceInterface
{
    /**
     * @param NotificationDto $notificationDto
     * @param WidgetInterface $widget
     * @return void
     */
    public function createTaskFromNotificationAndWidget(NotificationDto $notificationDto, WidgetInterface $widget): void;

}
