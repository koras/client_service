<?php

namespace App\Contracts\Services;

use App\Contracts\Models\HistorySendInterface;
use App\Contracts\Models\OrderInterface;
use App\Contracts\Models\OrderItemInterface;
use App\DTO\HistorySendDto;
use App\Enums\HistorySendStatusEnum;
use App\Enums\HistorySendTypeEnum;
use App\Services\Notification\Contracts\Objects\SenderResultDtoInterface;

interface HistorySendServiceInterface
{
    /**
     * @param HistorySendDto $dto
     * @return HistorySendInterface
     */
    public function create(HistorySendDto $dto): HistorySendInterface;

    /**
     * @param int|null $id
     * @param HistorySendStatusEnum $statusEnum
     * @return void
     */
    public function updateStatus(?int $id, HistorySendStatusEnum $statusEnum): void;

    /**
     * @param OrderInterface $order
     * @param HistorySendTypeEnum $type
     * @return int
     */
    public function getCountByOrderAndType(OrderInterface $order, HistorySendTypeEnum $type): int;

    /**
     * @param OrderItemInterface $orderItem
     * @param HistorySendTypeEnum $type
     * @return bool
     */
    public function isSentSuccessByOrderItemAndType(OrderItemInterface $orderItem, HistorySendTypeEnum $type): bool;

}
