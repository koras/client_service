<?php

namespace Tests\ForTest;

use App\Contracts\Repositories\OrderItemRepositoryInterface;
use App\DTO\OrderItemDto;
use App\Models\Order;
use App\Models\OrderItem;
use App\Traits\SnakeCasingTrait;
use Illuminate\Database\Eloquent\Model;
use Ramsey\Uuid\Uuid;

class OrderItemRepositoryTEST implements OrderItemRepositoryInterface
{
    use SnakeCasingTrait;

    public function __construct(
        protected Model $model
    )
    {
    }

    public function create(OrderItemDto $dto)
    {
        $item = new OrderItem();

        $item->message = $dto->message;
        $item->basket_key = $dto->basketKey;
        $item->product_id = $dto->productId;
        $item->quantity = $dto->quantity;
        $item->widget_order_id = $dto->order->id;
        $item->delivery_type = $dto->deliveryType;
        $item->recipient_type = $dto->recipientTypeEnum->value;
        $item->recipient_name = $dto->recipientName;
        $item->recipient_email = $dto->recipientEmail;
        $item->recipient_msisdn = $dto->recipientMsisdn;
        $item->sender_name = $dto->senderName;
        $item->sender_email = $dto->senderEmail;
        $item->time_to_send = $dto->timeToSend;
        $item->delivered_at = $dto->deliveredAt;
        $item->amount = $dto->amount;
        $item->tiberium_order_id = $dto->tiberiumOrderId;
        $item->cover = $dto->cover;
        $item->utm = $dto->utm;

        return $item;
    }

}