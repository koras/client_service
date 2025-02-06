<?php

namespace App\DTO;

use App\Contracts\DTO\ArrayableDtoInterface;
use App\Contracts\Models\OrderInterface;
use App\Enums\RecipientTypeEnum;

readonly class OrderItemDto implements ArrayableDtoInterface
{
    public function __construct(
        public ?string $message,
        public OrderInterface $order,
        public string $basketKey,
        public string $productId,
        public int $quantity,
        public array $deliveryType,
        public RecipientTypeEnum $recipientTypeEnum,
        public string $recipientName,
        public ?string $recipientEmail,
        public ?string $recipientMsisdn,
        public ?string $senderName,
        public ?string $senderEmail,
        public \DateTime $timeToSend,
        public ?\DateTime $deliveredAt,
        public int $amount,
        public ?int $tiberiumOrderId,
        public string $cover,
        public ?string $utm,
        public int $flexibleNominal,
    )
    {
    }


    public function toArray(): array
    {
        return [
            'message' => $this->message,
            'basketKey' => $this->basketKey,
            'productId' => $this->productId,
            'quantity' => $this->quantity,
            'widgetOrderId' => $this->order->id,
            'deliveryType' => $this->deliveryType,
            'recipientType' => $this->recipientTypeEnum,
            'recipientName' => $this->recipientName,
            'recipientEmail' => $this->recipientEmail,
            'recipientMsisdn' => $this->recipientMsisdn,
            'senderName' => $this->senderName,
            'senderEmail' => $this->senderEmail,
            'timeToSend' => $this->timeToSend,
            'deliveredAt' => $this->deliveredAt,
            'amount' => $this->amount,
            'tiberiumOrderId' => $this->tiberiumOrderId,
            'cover' => $this->cover,
            'utm' => $this->utm,
            'flexibleNominal' => $this->flexibleNominal,
        ];
    }

}
