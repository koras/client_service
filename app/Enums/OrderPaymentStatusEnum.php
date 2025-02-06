<?php

namespace App\Enums;

enum OrderPaymentStatusEnum: string
{
    case Created = 'created';
    case Canceled = 'canceled';
    case Succeeded = 'succeeded';

    public static function isSucceeded(string $paymentStatus): bool
    {
        $paymentStatusEnum = self::tryFrom($paymentStatus);
        if ($paymentStatusEnum == self::Succeeded) {
            return true;
        }

        return false;
    }

    public static function wasReceivedCallback(string $paymentStatus): bool
    {
        $paymentStatusEnum = self::tryFrom($paymentStatus);
        if (in_array($paymentStatusEnum, [self::Canceled, self::Succeeded])) {
            return true;
        }

        return false;
    }
}
