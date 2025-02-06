<?php

namespace App\Services;

use App\Contracts\Models\HistorySendInterface;
use App\Contracts\Models\OrderInterface;
use App\Contracts\Models\OrderItemInterface;
use App\Contracts\Repositories\HistorySendRepositoryInterface;
use App\Contracts\Services\HistorySendServiceInterface;
use App\DTO\HistorySendDto;
use App\Enums\HistorySendStatusEnum;
use App\Enums\HistorySendTypeEnum;
use App\Services\Notification\Contracts\Objects\SenderResultDtoInterface;
use Exception;

readonly class HistorySendService implements HistorySendServiceInterface
{
    public function __construct(
        private HistorySendRepositoryInterface $historySendRepository
    )
    {
    }


    /**
     * @param HistorySendDto $dto
     * @return HistorySendInterface
     * @throws Exception
     */
    public function create(HistorySendDto $dto): HistorySendInterface
    {
        $historySend = $this->historySendRepository->create($dto);
        if (!$historySend) {
            throw new Exception('create historySendError');
        }

        return $historySend;
    }

    /**
     * @param int|null $id
     * @param HistorySendStatusEnum $statusEnum
     * @return void
     */
    public function updateStatus(?int $id, HistorySendStatusEnum $statusEnum): void
    {
        if (is_null($id)) {
            return;
        }

        $data = [
            'status' => $statusEnum->value,
        ];
        $this->historySendRepository->update($id, $data);
    }

    /**
     * Получить количество записей по orderId, HistorySendType, в статусе Success (2)
     *
     * @param OrderInterface $order
     * @param HistorySendTypeEnum $type
     * @return int
     */
    public function getCountByOrderAndType(OrderInterface $order, HistorySendTypeEnum $type): int
    {
        $historySends = $this->historySendRepository->findBy([
            'order_id' => $order->id,
            'type' => $type->name,
            'status' => HistorySendStatusEnum::Success->value
        ]);

        return $historySends->count();
    }

    /**
     * Проверить, что сообщение в статусе Success(2), по orderItem и HistorySendType
     *
     * @param OrderItemInterface $orderItem
     * @param HistorySendTypeEnum $type
     * @return bool
     */
    public function isSentSuccessByOrderItemAndType(OrderItemInterface $orderItem, HistorySendTypeEnum $type): bool
    {
        $historySends = $this->historySendRepository->findBy([
            'order_item_id' => $orderItem->id,
            'type' => $type->name,
            'status' => HistorySendStatusEnum::Success->value
        ]);

        return $historySends->count() >= 1;
    }

}
