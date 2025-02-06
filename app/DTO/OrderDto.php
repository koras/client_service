<?php

namespace App\DTO;

use App\Contracts\DTO\ArrayableDtoInterface;
use App\Contracts\Models\WidgetInterface;
use App\Enums\OrderPaymentStatusEnum;
use App\Enums\OrderTypeEnum;
use App\Http\Requests\CreateOrderRequest;
use App\Traits\UniqueGeneratorTrait;
use Illuminate\Support\Collection;
use App\Repositories\PromoCodesRepository;

readonly class OrderDto implements ArrayableDtoInterface
{
    use UniqueGeneratorTrait;
    public string $trackingNumber;

    public function __construct(
        public ?OrderTypeEnum $orderTypeEnum,
        public OrderPaymentStatusEnum $paymentStatusEnum,
        public string $widgetId,
        public Collection $orderItems,
        public int|null $promoCodeId
    )
    {
        $this->trackingNumber = $this->generateUniqueTrackingNumber();
    }


    public static function fromCreateRequest(CreateOrderRequest $request, WidgetInterface $widget, $promoCodeProduct): self
    {
        $array = $request->all();
        $itemData = $array[0];

        $orderType = OrderTypeEnum::tryFrom($itemData['certificate']['orderType'] ?? null);
        $paymentStatus = OrderPaymentStatusEnum::Created;

        $orderItems = new Collection();
        foreach ($array as $item) {
            $orderItems->add($item);
        }

        if($promoCodeProduct){
            $objectPromoCodeProduct = json_decode($promoCodeProduct);
            $promoCodeId = $objectPromoCodeProduct->id;
        }

        return new self(
            $orderType,
            $paymentStatus,
            $widget->id,
            $orderItems,
            $promoCodeId ?? null
        );
    }

    public function toArray(): array
    {
        return [
            'type' => $this->orderTypeEnum,
            'widgetId' => $this->widgetId,
            'paymentStatus' => $this->paymentStatusEnum,
            'trackingNumber' => $this->trackingNumber,
            'promoCodeId' => $this->promoCodeId,
        ];
    }
}
