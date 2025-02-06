<?php

namespace App\Contracts\Services\Bitrix24;

use App\Contracts\DTO\TaskDtoInterface;
use App\Contracts\Models\WidgetInterface;
use App\DTO\NotificationDto;

interface BitrixTaskDtoBuilderInterface
{
    public function build(NotificationDto $notificationDto, WidgetInterface $widget): TaskDtoInterface;

}
