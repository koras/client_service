<?php

namespace App\DTO;

use App\Contracts\Models\OrderInterface;
use Illuminate\Contracts\Support\Arrayable;

readonly class CreatePaymentDataDto implements Arrayable
{

    public function __construct(
        public OrderInterface $order,
        public RequestHostDto $hostDto,
    )
    {
    }

    /**
     * @param OrderInterface $order
     * @param RequestHostDto $hostDto
     * @return self
     */
    public static function fromOrderAndHostDto(OrderInterface $order, RequestHostDto $hostDto): self
    {
        return new self(
            $order,
            $hostDto
        );
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return [
            'amount' => [
                'value' => $this->order->getTotalSum(),
                'currency' => 'RUB',
            ],
            'capture' => true,
            'confirmation' => [
                'type' => 'embedded',
            ],
            'description' => 'Оплата заказа №' . $this->order->tracking_number . ' для ' . $this->order->orderItems->first()->sender_email,
            'metadata' => [
                'order_id' => $this->order->tracking_number,
                'http_host' => $this->hostDto->httpHost,
                'host' => $this->hostDto->host,
            ],
        ];
    }

}