<?php

namespace App\Enums;

enum ProductApiResponseStatusEnum
{
    case ok;
    case error;

    public static function isError(string $status): bool
    {
        if ($status == self::error->name) {
            return true;
        }

        return false;
    }

}
