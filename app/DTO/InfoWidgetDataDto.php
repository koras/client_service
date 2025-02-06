<?php

namespace App\DTO;

use App\Contracts\Models\WidgetInterface;
use Illuminate\Support\Collection;

readonly class InfoWidgetDataDto
{
    public function __construct(
        public WidgetInterface $widget,
        public string $rules,
        public Collection $usageRules,
        public ?int $activeNominal,
        public array $covers,
        public array $amounts,
        public array $limits,
    )
    {
    }

}
