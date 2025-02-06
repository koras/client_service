<?php

namespace Tests\ForTest;

use App\Contracts\DTO\ArrayableDtoInterface;
use App\Contracts\Models\OrderInterface;
use App\Contracts\Repositories\OrderRepositoryInterface;
use App\DTO\OrderDto;
use App\Enums\OrderPaymentStatusEnum;
use App\Models\Order;
use App\Traits\SnakeCasingTrait;
use App\ValueObjects\OrderPaymentObj;
use Illuminate\Database\Eloquent\Model;
use Ramsey\Uuid\Uuid;

class OrderRepositoryTEST implements OrderRepositoryInterface
{
    use SnakeCasingTrait;

    public function __construct(
        protected Model $model
    )
    {
    }

    public function create(OrderDto $dto)
    {
        $order = new Order();
        $order->id = Uuid::uuid4();
        $order->widget_id = $dto->widgetId;
        $order->payment_status = $dto->paymentStatusEnum->value;
        $order->type = $dto->orderTypeEnum?->value;

        return $order;
    }

    public function updatePaymentData(OrderInterface $order, OrderPaymentObj $paymentObj): void
    {
        $order->payment_status = $paymentObj->status;
        $order->payment_data = $paymentObj->data;
        $order->payment_token = $paymentObj->token;
    }

    public function updatePaymentStatus(OrderInterface $order, OrderPaymentStatusEnum $statusEnum): void
    {
        $order->payment_status = $statusEnum->value;
        return;
    }
}