<?php

namespace App\Contracts\Models;

use DateTime;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property string $order_id
 * @property DateTime $timestamp
 * @property int $status
 * @property string $client_id
 * @property string $recipient
 * @property string|null $type
 * @property int|null $order_item_id
 * @property DateTime $updated_at
 * @property string|null $send_type
 * @property string|null $external_id
 * @property string|null $service_status
 * @property OrderInterface $order
 * @property OrderItemInterface|null $orderItem
 */
interface HistorySendInterface
{
    public function order(): BelongsTo;
    public function orderItem(): BelongsTo;
}