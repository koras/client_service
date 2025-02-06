<?php

namespace App\Contracts\Services;

use App\Contracts\Models\OrderInterface;
use App\DTO\OrderDto;
use App\DTO\RequestHostDto;
use App\ValueObjects\OrderPaymentObj;

interface OrderServiceInterface
{
    public function createOrderProcess(OrderDto $orderDto, RequestHostDto $hostDto): ?OrderPaymentObj;

    public function createOrderInDB(OrderDto $createOrderDto): ?OrderInterface;

    public function resendOrderToVendor(OrderInterface $order): bool;

}
