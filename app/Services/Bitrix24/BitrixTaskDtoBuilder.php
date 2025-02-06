<?php

namespace App\Services\Bitrix24;

use App\Contracts\DTO\TaskDtoInterface;
use App\Contracts\Models\WidgetInterface;
use App\Contracts\Repositories\SystemSettingRepositoryInterface;
use App\Contracts\Services\Bitrix24\BitrixTaskDtoBuilderInterface;
use App\DTO\BitrixCreateTaskDto;
use App\DTO\NotificationDto;
use App\Enums\BitrixPriorityEnum;
use App\Enums\SettingBitrixNamesEnum;
use App\Enums\SettingSectionEnum;
use Carbon\Carbon;

class BitrixTaskDtoBuilder implements BitrixTaskDtoBuilderInterface
{
    public function __construct(
        private readonly SystemSettingRepositoryInterface $settingRepository,
    )
    {
    }

    private WidgetInterface $widget;
    private array $notificationData;

    public function build(NotificationDto $notificationDto, WidgetInterface $widget): TaskDtoInterface
    {
        $this->widget = $widget;
        $this->notificationData = $notificationDto->data;

        $settings = $this->getBitrixSettingsAsArray();
        $groupId = !empty($settings[SettingBitrixNamesEnum::Group->value]) ? $settings[SettingBitrixNamesEnum::Group->value][0] : config('bitrix-users.group');
        $responsible = !empty($settings[SettingBitrixNamesEnum::Responsible->value]) ? $settings[SettingBitrixNamesEnum::Responsible->value][0] : config('bitrix-users.responsible');
        $creator = !empty($settings[SettingBitrixNamesEnum::Creator->value]) ? $settings[SettingBitrixNamesEnum::Creator->value][0] : config('bitrix-users.creator');
        $accomplices = !empty($settings[SettingBitrixNamesEnum::Accomplicies->value]) ? $settings[SettingBitrixNamesEnum::Accomplicies->value] : config('bitrix-users.accomplices');
        $auditors = !empty($settings[SettingBitrixNamesEnum::Auditors->value]) ? $settings[SettingBitrixNamesEnum::Auditors->value] : config('bitrix-users.auditors');

        $title = $this->formatTitleFromNotificationData();
        $description = $this->formatDescriptionFromNotificationData();

        $deadline = Carbon::now()->addDays(1)->format('d.m.Y H:i:s');

        return new BitrixCreateTaskDto(
            $title,
            $description,
            BitrixPriorityEnum::Medium->value,
            $responsible,
            $creator,
            $accomplices,
            $auditors,
            $groupId,
            $deadline,
            config('bitrix-api.tagValue')
        );

    }

    private function getBitrixSettingsAsArray(): array
    {
        $settings = $this->settingRepository->getSettingsBySection(SettingSectionEnum::Bitrix);
        return $settings->pluck('value', 'name')->toArray();
    }

    private function formatDescriptionFromNotificationData(): string
    {

        return 'Обращение в поддержку. Виджет:' . $this->widget->name . '<br>' .
            ' Клиент: ' . $this->notificationData['name'] . '<br>' .
            ' Email: ' . $this->notificationData['email'] . '<br>' .
            ' Телефон: ' . $this->notificationData['phone'] . '<br>' .
            ' Сообщение: ' . $this->notificationData['message']
            ;
    }

    private function formatTitleFromNotificationData(): string
    {
        return 'Виджет ' . $this->widget->name . '. Обращение в поддержку';
    }
}