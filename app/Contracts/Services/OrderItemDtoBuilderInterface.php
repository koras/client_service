<?php

namespace App\Contracts\Services;

use App\Contracts\Models\OrderInterface;
use App\Contracts\Models\WidgetInterface;
use App\DTO\OrderItemDto;

interface OrderItemDtoBuilderInterface
{
    public function build(array $requestOrderItem, OrderInterface $order, WidgetInterface $widget): OrderItemDto;

}
