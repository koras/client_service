<?php

namespace App\Contracts\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property string $id
 * @property int $order_item_id
 * @property string $serial
 * @property string $expire_at
 * @property string $created_at
 * @property string|null $amount
 * @property string|null $pdf_generated_at
 * @property string|null $delivered_at
 * @property string $cover_path
 * @property string|null $pin
 * @property string|null $nominal
 * @property string|null $currency
 * @property string|null $barcode
 * @property OrderItemInterface $orderItem
 */
interface CertificateInterface
{
    public function orderItem(): BelongsTo;
}