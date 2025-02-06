<?php

namespace App\DTO;

use App\Contracts\DTO\ArrayableDtoInterface;

readonly class UsageRuleItemDto implements ArrayableDtoInterface
{
    public function __construct(
        public string $icon,
        public string $text
    )
    {
    }

    public function toArray(): array
    {
        return [
            'icon' => $this->icon,
            'text' => $this->text
        ];
    }
}