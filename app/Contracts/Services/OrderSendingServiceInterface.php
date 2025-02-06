<?php

namespace App\Contracts\Services;

use App\Enums\DeliveryTypeEnum;
use Illuminate\Http\UploadedFile;

/**
 * Сервис для заказов с типом Рассылка
 */
interface OrderSendingServiceInterface
{
    /**
     * @return array
     */
    public function getValidRows(): array;

    /**
     * @return array
     */
    public function getErrorRows(): array;

    public function importFromXls(UploadedFile $file, DeliveryTypeEnum $recipientTypeEnum): bool;
}