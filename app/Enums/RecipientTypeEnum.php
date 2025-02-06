<?php

namespace App\Enums;

enum RecipientTypeEnum: string
{
    case Me = 'me';
    case Other = 'other';

    public static function values(): array
    {
        return array_column(self::cases(),'value' );
    }
}
