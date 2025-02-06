<?php

namespace App\Contracts\Services;

use App\Contracts\Models\OrderItemInterface;
use Exception;

interface VendorOrderServiceInterface
{
    /**
     * Создать новый заказ в ПЦ по OrderItem
     * в ПЦ для каждого OrderItem создаем отдельный заказ
     *
     * @param OrderItemInterface $orderItem
     * @return int $externalOrderId
     * @throws Exception
     */
    public function createOrderFromOrderItem(OrderItemInterface $orderItem): int;

}