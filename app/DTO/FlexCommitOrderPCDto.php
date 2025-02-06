<?php

namespace App\DTO;

use App\Contracts\DTO\ArrayableDtoInterface;

readonly class FlexCommitOrderPCDto implements ArrayableDtoInterface
{
    public function __construct(
        public int $orderNumber,
        public ProductDto $productDto,
        public int $quantity,
        public ?string $email,
        public ?string $phoneNumber,
        public ?string $additionalInfo,
        public ?int $cardPaymentAmount,
    )
    {
    }

    public function toArray(): array
    {
        return [
            'orderNumber' => $this->orderNumber,
            'orderItems' => [
                [
                    'salePrice' => $this->productDto->price,
                    'salesPositionId' => $this->productDto->positionId,
                    'nominal' => $this->productDto->nominal,
                    'quantity' => $this->quantity
                ]
            ],
            'email' => $this->email ?? '',
            'phoneNumber' => $this->phoneNumber ?? '',
            'additionalInfo' => $this->additionalInfo ?? '',
            'cardPaymentAmount' => $this->cardPaymentAmount ?? 0,
        ];
    }
}
