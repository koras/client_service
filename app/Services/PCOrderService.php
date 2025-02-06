<?php

namespace App\Services;

use App\Contracts\Models\OrderItemInterface;
use App\Contracts\Services\CommitOrderPCDtoFactoryInterface;
use App\Contracts\Services\PCApiServiceInterface;
use App\Contracts\Services\VendorOrderServiceInterface;
use App\Logging\WidgetLogObject;
use Error;
use Exception;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Класс для работы с заказами у поставщика (ПЦ)
 */
readonly class PCOrderService implements VendorOrderServiceInterface
{
    public function __construct(
        private CommitOrderPCDtoFactoryInterface $commitOrderPCDtoFactory,
        private PCApiServiceInterface $PCApiService,
    )
    {
    }

    /**
     * Создать новый заказ в ПЦ по OrderItem
     * в ПЦ для каждого OrderItem создаем отдельный заказ
     *
     * @param OrderItemInterface $orderItem
     * @return int $externalOrderId
     * @throws Exception
     */
    public function createOrderFromOrderItem(OrderItemInterface $orderItem): int
    {
        try {
            $commitOrderDto = $this->commitOrderPCDtoFactory->createDtoFromOrderItem($orderItem);

            $this->PCApiService
                ->commitOrder($commitOrderDto);

            /*
            if ($orderItem->flexible_nominal) {
                $this->PCApiService
                    ->flexCommitOrder($commitOrderDto);
            } else {
                $this->PCApiService
                    ->commitOrder($commitOrderDto);
            }
*/
            return $commitOrderDto->orderNumber;
        } catch (Throwable|Error $e) {
            $log = WidgetLogObject::make('Error createOrderFromOrderItem for orderItem: ' . $orderItem->id . ' Error: ' . $e->getMessage(), 'createOrder', $orderItem);
            Log::error($log->message, $log->toContext());

            throw new Exception('Error createOrderFromOrderItem for orderItem: ' . $orderItem->id . ' Error: ' . $e->getMessage());
        }

    }

}
