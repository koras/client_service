<?php

namespace App\Contracts\JobsHandlers;

use App\Contracts\Models\OrderInterface;
use App\Contracts\Models\OrderItemInterface;
use Exception;

interface PaidOrderTransferToVendorHandlerInterface
{

    /**
     * @param string $orderId
     * @return OrderInterface
     */
    public function getOrderById(string $orderId): OrderInterface;

    /**
     * Публикуем новый заказ в ПЦ
     *
     * @param OrderItemInterface $orderItem
     * @return int
     * @throws Exception
     */
    public function sendOrderItemToVendor(OrderItemInterface $orderItem): int;

    /**
     * Сохраняем externalOrderId(id заказа в ПЦ) для OrderItem
     *
     * @param OrderItemInterface $orderItem
     * @param int $externalOrderId
     * @return void
     */
    public function saveExternalOrderIdInOrderItem(OrderItemInterface $orderItem, int $externalOrderId): void;

    /**
     * @param OrderInterface $order
     * @return void
     * @throws Exception
     */
    public function reduceProductsByOrder(OrderInterface $order): void;

}
