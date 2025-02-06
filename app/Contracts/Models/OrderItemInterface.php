<?php

namespace App\Contracts\Models;

use App\Models\Certificate;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Ramsey\Uuid\Uuid;

/**
 * @property int $id
 * @property string|null $message
 * @property string $basket_key
 * @property string $product_id
 * @property int $quantity
 * @property string $delivery_type
 * @property string $recipient_type
 * @property string $recipient_name
 * @property string $recipient_email
 * @property string $recipient_msisdn
 * @property string $sender_name
 * @property string $sender_email
 * @property string $time_to_send
 * @property string|null $delivered_at
 * @property Uuid $widget_order_id
 * @property int $amount
 * @property int $tiberium_order_id
 * @property string $cover
 * @property string $utm
 * @property OrderInterface $order
 * @property Collection|Certificate[] $certificates
 * @property string $flexible_nominal
 */
interface OrderItemInterface
{
    public function order(): BelongsTo;

    public function certificates(): HasMany;

    public function widgetOrder(): BelongsTo;
}
