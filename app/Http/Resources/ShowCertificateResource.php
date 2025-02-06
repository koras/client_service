<?php

namespace App\Http\Resources;

use App\DTO\ShowCertificateDataDto;
use App\Traits\PathGeneratorTrait;
use Illuminate\Http\Resources\Json\JsonResource;

class ShowCertificateResource extends JsonResource
{
    use PathGeneratorTrait;
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray($request): array
    {
        /** @var ShowCertificateDataDto $this */
        $certificate = $this->certificate;
        $widget = $certificate->orderItem->order->widget;

        return [
            'id' => $certificate->id,
            'serial' => $certificate->serial,
            'expire_at' => $certificate->expire_at,
            'amount' => $certificate->amount,
            'pin' => $certificate->pin,
            'cover' => $this->cover,
            'sender_name' => $certificate->orderItem->sender_name,
            'recipient_name' => $certificate->orderItem->recipient_name,
            'recipient_type' => $certificate->orderItem->recipient_type,
            'message' => $certificate->orderItem->message,
            'faq' => $widget->faq,
            'favicon' => $this->getFaviconDirUrl($widget) . '/' . $widget->favicon_image,
            'support_email' => $widget->support_email,
            'support_msisdn' => $widget->support_tel_number,
            'template' => $widget->mailTemplate->name,
            'qr' => $this->qr,
            'barcode' => $this->barcode,
            'barcodePdf417' => $this->barcodePdf417,
            'sms_pin' => $certificate->orderItem->order->widget->send_sms_pin
        ];
    }
}
