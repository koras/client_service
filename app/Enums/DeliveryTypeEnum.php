<?php

namespace App\Enums;

enum DeliveryTypeEnum: string
{
    case Email = 'email';
    case Phone = 'phone';
    case Sms = 'sms';

    public static function values(): array
    {
        return array_column(self::cases(),'value' );
    }
}
