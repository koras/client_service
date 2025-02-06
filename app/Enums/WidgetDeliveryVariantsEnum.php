<?php

namespace App\Enums;

enum WidgetDeliveryVariantsEnum
{
    case Email;
    case SMS;
    case Charity;

    public static function isCharity(string $deliveryVariant): bool
    {
        return $deliveryVariant == self::Charity->name;
    }
}
