<?php

namespace App\Enums;

enum SettingBitrixNamesEnum: string
{
    case Group = 'group'; // Проект
    case Responsible = 'responsible'; // Исполнитель
    case Creator = 'creator'; // Постановщик
    case Auditors = 'auditors'; // наблюдатели
    case Accomplicies = 'accomplicies'; // Соисполнители

    public static function isValid(string $value): bool
    {
        return in_array($value, self::values(), true);
    }


    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
