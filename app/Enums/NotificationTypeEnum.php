<?php

namespace App\Enums;

enum NotificationTypeEnum: string
{
    case Support = 'support';

    public function getSubject(): string
    {
        return match ($this) {
            self::Support => 'Запрос в поддержку с виджета ',
            default => 'Уведомление '
        };
    }
}
