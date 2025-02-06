<?php

namespace App\Contracts\Repositories;

use App\Contracts\Models\OrderInterface;
use App\Enums\OrderPaymentStatusEnum;
use App\ValueObjects\OrderPaymentObj;

interface OrderRepositoryInterface
{
    /**
     * @param OrderInterface $order
     * @param OrderPaymentObj $paymentObj
     * @return void
     */
    public function updatePaymentData(OrderInterface $order, OrderPaymentObj $paymentObj): void;

    /**
     * @param OrderInterface $order
     * @param OrderPaymentStatusEnum $statusEnum
     * @return void
     */
    public function updatePaymentStatus(OrderInterface $order, OrderPaymentStatusEnum $statusEnum): void;

    /**
     * Найти заказ по id или tracking_number
     *
     * @param string $identificator
     * @return OrderInterface|null
     */
    public function findByIdOrTrackingNumber(string $identificator): ?OrderInterface;

    /**
     * Найти заказ по tracking_number
     *
     * @param string $trackingNumber
     * @return OrderInterface|null
     */
    public function findByTrackingNumber(string $trackingNumber): ?OrderInterface;

}

