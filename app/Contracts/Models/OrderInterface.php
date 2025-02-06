<?php

namespace App\Contracts\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property string $id
 * @property string $widget_id
 * @property string $payment_status
 * @property string|null $payment_token
 * @property string|null $tracking_number
 * @property array|null $payment_data
 * @property null|string $type
 * @property WidgetInterface $widget
 * @property Collection|OrderItemInterface[] $orderItems
 * @property null|int $promo_code_id
 *
 */
interface OrderInterface
{
    public function orderItems(): HasMany;

    public function widget(): BelongsTo;

    public function getTotalSum(): int;

    public function wasReceivedCallback(): bool;
}
