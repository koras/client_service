<?php

namespace App\Contracts\Services;

use App\Contracts\Models\OrderItemInterface;
use App\DTO\CommitOrderPCDto;
use App\DTO\FlexCommitOrderPCDto;

interface CommitOrderPCDtoFactoryInterface
{
    /**
     * @param OrderItemInterface $orderItem
     * @return CommitOrderPCDto|FlexCommitOrderPCDto
     */
    public function createDtoFromOrderItem(OrderItemInterface $orderItem): CommitOrderPCDto|FlexCommitOrderPCDto;
}
