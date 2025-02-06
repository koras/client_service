<?php

namespace App\ValueObjects;

use App\Contracts\Models\WidgetInterface;
use App\Traits\PathGeneratorTrait;
use Illuminate\Contracts\Support\Arrayable;
use JsonSerializable;

class WidgetSaleLabelObj implements Arrayable, JsonSerializable
{
    use PathGeneratorTrait;

    public function __construct(
        public ?string $saleImage,
        public ?string $saleColor,
        public ?array $saleProductIds,
    )
    {
    }

    public static function fromWidget(WidgetInterface $widget): ?self
    {
        $saleImage = $widget->sale_image;
        $saleColor = $widget->sale_color;
        $saleProductIds = $widget->sale_product_ids;

        if (empty($saleImage) || empty($saleColor) || empty($saleProductIds)) {
            return null;
        }

        return new self(
            self::getSaleImageDirUrl() . '/' . $saleImage,
            $saleColor,
            $saleProductIds
        );
    }

    public function issetNullProperties(): bool
    {
        if (in_array(null, [$this->saleColor, $this->saleImage, $this->saleProductIds])) {
            return true;
        }

        return false;
    }

    public function toArray(): array
    {
        return [
            'saleImage' => $this->saleImage,
            'saleColor' => $this->saleColor,
            'saleProductIds' => $this->saleProductIds
        ];
    }

    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}