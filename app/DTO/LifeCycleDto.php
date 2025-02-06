<?php

namespace App\DTO;

use App\Contracts\DTO\ArrayableDtoInterface;

readonly class LifeCycleDto implements ArrayableDtoInterface
{
    public function __construct(
        public string $orderId,
        public string $system,
        public string $status,
        public string $value = "",
    ) {
    }


    public function toArray(): array
    {
        return [
            'order_id' => $this->orderId,
            'system' => $this->system,
            'status' => $this->status,
            'value' => $this->value,
        ];
    }
}
