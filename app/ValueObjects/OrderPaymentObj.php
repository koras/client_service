<?php

namespace App\ValueObjects;

use App\Enums\OrderPaymentStatusEnum;
use Illuminate\Contracts\Support\Arrayable;
use YooKassa\Request\Payments\CreatePaymentResponse;
use YooKassa\Request\Payments\PaymentResponse;

readonly class OrderPaymentObj implements Arrayable
{
    public function __construct(
        public string $status,
        public string $token,
        public array $data
    )
    {
    }

    public static function fromCreatePaymentResponse(CreatePaymentResponse $response): self
    {
        return new self(
            $response->getPaid(),
            $response->getConfirmation()->getConfirmationToken(),
            $response->toArray()
        );
    }

    public function toArray(): array
    {
        return [
            'payment_status' => $this->status,
            'payment_token' => $this->token,
            'payment_data' => $this->data
        ];
    }
}