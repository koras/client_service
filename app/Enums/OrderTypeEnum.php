<?php

namespace App\Enums;

enum OrderTypeEnum: string
{
    case Manual = 'manual';
    case Sending = 'sending';

    public static function values(): array
    {
        return array_column(self::cases(),'value' );
    }
}
