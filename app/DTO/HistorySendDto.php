<?php

namespace App\DTO;

use App\Contracts\DTO\ArrayableDtoInterface;
use App\Contracts\Models\OrderInterface;
use App\Contracts\Models\OrderItemInterface;
use App\Enums\DeliveryTypeEnum;
use App\Enums\HistorySendStatusEnum;
use App\Enums\HistorySendTypeEnum;

class HistorySendDto implements ArrayableDtoInterface
{
    public function __construct(
        public string $orderId,
        public HistorySendStatusEnum $status,
        public string $clientId,
        public string $recipient,
        public ?HistorySendTypeEnum $type,
        public ?int $orderItemId,
        public ?DeliveryTypeEnum $sendType,
        public ?string $externalId,
        public ?string $serviceStatus,
        public ?string $certificateId
    ) {
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return [
            'order_id' => $this->orderId,
            'status' => $this->status,
            'client_id' => $this->clientId,
            'recipient' => $this->recipient,
            'type' => $this->type,
            'order_item_id' => $this->orderItemId,
            'send_type' => $this->sendType,
            'external_id' => $this->externalId,
            'service_status' => $this->serviceStatus,
            'certificateId' => $this->certificateId,
        ];
    }

    /**
     * @param $orderItem
     * @return static
     */
    public static function forPinSmsMessage($orderItem, $certificate): self
    {

        $order = $orderItem->order;
        return new self(
            $order->id,
            HistorySendStatusEnum::Error,
            $order->widget->client_id,
            $orderItem->recipient_msisdn,
            HistorySendTypeEnum::send_pin_sms,
            $orderItem->id,
            DeliveryTypeEnum::Sms,
            null,
            null,
            $certificate->id
        );
    }

}
