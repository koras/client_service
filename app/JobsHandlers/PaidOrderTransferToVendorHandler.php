<?php

namespace App\JobsHandlers;

use App\Contracts\JobsHandlers\PaidOrderTransferToVendorHandlerInterface;
use App\Contracts\Models\OrderInterface;
use App\Contracts\Models\OrderItemInterface;
use App\Contracts\Repositories\OrderItemRepositoryInterface;
use App\Contracts\Repositories\OrderRepositoryInterface;
use App\Contracts\Services\ProductsApiServiceInterface;
use App\Contracts\Services\VendorOrderServiceInterface;
use Exception;

readonly class PaidOrderTransferToVendorHandler implements PaidOrderTransferToVendorHandlerInterface
{
    public function __construct(
        private VendorOrderServiceInterface $vendorOrderService,
        private ProductsApiServiceInterface $productsApiService,
        private OrderRepositoryInterface $orderRepository,
        private OrderItemRepositoryInterface $orderItemRepository,
    )
    {
    }

    /**
     * @param string $orderId
     * @return OrderInterface
     */
    public function getOrderById(string $orderId): OrderInterface
    {
        return $this->orderRepository->find($orderId);
    }

    /**
     * @param OrderItemInterface $orderItem
     * @return int
     * @throws Exception
     */
    public function sendOrderItemToVendor(OrderItemInterface $orderItem): int
    {
        return $this->vendorOrderService->createOrderFromOrderItem($orderItem);
    }


    /**
     * @param OrderInterface $order
     * @return void
     * @throws Exception
     */
    public function reduceProductsByOrder(OrderInterface $order): void
    {
        $orderItems = $order->orderItems;
        $reduceData = [];
        foreach ($orderItems as $orderItem) {
            $reduceData[] = [
                'productId' => $orderItem->product_id,
                'quantity' => $orderItem->quantity
            ];
        }
        $this->productsApiService->reduceProducts($reduceData);
    }

    /**
     * Сохраняем externalOrderId для OrderItem
     *
     * @param OrderItemInterface $orderItem
     * @param int $externalOrderId
     * @return void
     */
    public function saveExternalOrderIdInOrderItem(OrderItemInterface $orderItem, int $externalOrderId): void
    {
        $this->orderItemRepository
            ->update($orderItem->id, ['tiberium_order_id' => $externalOrderId]);
    }
}