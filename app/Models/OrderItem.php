<?php

namespace App\Models;

use App\Casts\Serializeble;
use App\Contracts\Models\OrderItemInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OrderItem extends Model implements OrderItemInterface
{
    use HasFactory;
    public const UPDATED_AT = null;
    public const CREATED_AT = null;

    protected $table = 'order_item';

    protected $fillable = [
        'message',
        'basket_key',
        'product_id',
        'quantity',
        'delivery_type',
        'recipient_type',
        'recipient_name',
        'recipient_email',
        'recipient_msisdn',
        'sender_name',
        'sender_email',
        'time_to_send',
        'delivered_at',
        'widget_order_id',
        'amount',
        'tiberium_order_id',
        'cover',
        'utm',
        'flexible_nominal'
    ];

    protected $casts = [
        'delivery_type' => Serializeble::class,
        'time_to_send' => 'datetime',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'widget_order_id', 'id');
    }

    public function certificates(): HasMany
    {
        return $this->hasMany(Certificate::class);
    }

    public function widgetOrder(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'widget_order_id', 'id');
    }
}
