<?php

namespace App\DTO;

use App\Contracts\DTO\ArrayableDtoInterface;

class PreviewPdfDataDto implements ArrayableDtoInterface
{
    public function __construct(
        public string $widgetId,
        public int $templateId,
        public string $nominal,
        public string $currency,
        public bool $download,
    )
    {
    }

    public function toArray(): array
    {
        return [
            'widgetId' => $this->widgetId,
            'templateId' => $this->templateId,
            'nominal' => $this->nominal,
            'currency' => $this->currency,
            'download' => $this->download,
        ];
    }
}