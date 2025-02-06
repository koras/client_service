<?php

namespace App\DTO;

use App\Contracts\DTO\ArrayableDtoInterface;
use App\Contracts\Models\WidgetInterface;
use App\Enums\DeliveryTypeEnum;
use App\Enums\NotificationTypeEnum;
use App\Http\Requests\SendSupportRequest;

class NotificationDto implements ArrayableDtoInterface
{
    public function __construct(
        public DeliveryTypeEnum $deliveryType,
        public NotificationTypeEnum $notificationType,
        public ?string $templatePath,
        public ?string $subject,
        public array $data,
    )
    {
    }

    public function toArray(): array
    {
        return [
            'deliveryType' => $this->deliveryType->value,
            'notificationType' => $this->notificationType->value,
            'templatePath' => $this->templatePath ?? null,
            'subject' => $this->subject ?? null,
            'data' => $this->data
        ];
    }

    public static function createSupportFromRequest(SendSupportRequest $request, WidgetInterface $widget): self
    {
        $data = [
            'name' => $request->Name,
            'email' => $request->Email,
            'message' => $request->Message,
            'phone' => $request->Phone,
            'widgetId' => $widget->id,
        ];

        return new self(
            DeliveryTypeEnum::Email,
            NotificationTypeEnum::Support,
            null,
            null,
            $data
        );
    }

}