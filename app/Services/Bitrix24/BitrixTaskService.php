<?php

namespace App\Services\Bitrix24;

use App\Contracts\Models\WidgetInterface;
use App\Contracts\Services\Bitrix24\BitrixApiServiceInterface;
use App\Contracts\Services\Bitrix24\BitrixTaskDtoBuilderInterface;
use App\Contracts\Services\Bitrix24\BitrixTaskServiceInterface;
use App\DTO\NotificationDto;
use App\Logging\WidgetLogObject;
use Illuminate\Support\Facades\Log;

readonly class BitrixTaskService implements BitrixTaskServiceInterface
{
    public function __construct(
        private BitrixApiServiceInterface $bitrixApiService,
        private BitrixTaskDtoBuilderInterface $taskDtoBuilder,
    )
    {
    }

    /**
     * @param NotificationDto $notificationDto
     * @param WidgetInterface $widget
     * @return void
     */
    public function createTaskFromNotificationAndWidget(NotificationDto $notificationDto, WidgetInterface $widget): void
    {
        $log = WidgetLogObject::make('Start createTaskSupport for B24: ', 'sendSupport');
        Log::info($log->message, $log->toContext());

        $taskDto = $this->taskDtoBuilder->build($notificationDto, $widget);
        $taskData = $this->bitrixApiService->sendTask($taskDto);

        $taskId = !empty($taskData['result']['task']['id']) ? $taskData['result']['task']['id'] : 'undefined';

        $log = WidgetLogObject::make('Success createTaskSupport for B24: TaskId = ' . $taskId, 'sendSupport');
        Log::info($log->message, $log->toContext());
    }

}
