<?php

namespace App\Enums;


use YooKassa\Model\Notification\NotificationEventType;

enum YookassaCallbackEventTypeEnum: string
{
    /** Успешно оплачен покупателем, ожидает подтверждения магазином */
    case PaymentWaitingForCapture = NotificationEventType::PAYMENT_WAITING_FOR_CAPTURE;
    /** Успешно оплачен и подтвержден магазином */
    case PaymentSucceeded = NotificationEventType::PAYMENT_SUCCEEDED;
    /** Неуспех оплаты или отменен магазином */
    case PaymentCanceled = NotificationEventType::PAYMENT_CANCELED;
    /** Успешный возврат */
    case RefundSucceeded = NotificationEventType::REFUND_SUCCEEDED;
    /** Сделка перешла в статус closed */
    case DealClosed = NotificationEventType::DEAL_CLOSED;
    /** Выплата перешла в статус canceled */
    case PayoutCanceled = NotificationEventType::PAYOUT_CANCELED;
    /** Выплата перешла в статус succeeded */
    case PayoutSucceeded = NotificationEventType::PAYOUT_SUCCEEDED;

    public static function isValidForProcess(string $eventStatus): bool
    {
        $eventStatusEnum = self::tryFrom($eventStatus);
        if (in_array($eventStatusEnum, [self::PaymentSucceeded, self::PaymentCanceled])) {
            return true;
        }

        return false;
    }

    public static function isSucceeded(string $eventStatus): bool
    {
        $eventStatusEnum = self::tryFrom($eventStatus);
        if ($eventStatusEnum == self::PaymentSucceeded) {
            return true;
        }

        return false;
    }
}
