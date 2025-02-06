<?php

namespace App\Models;

use App\Casts\OrderPayment;
use App\Contracts\Models\OrderInterface;
use App\Enums\OrderPaymentStatusEnum;
use App\Enums\OrderTypeEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Order extends Model implements OrderInterface
{
    use HasFactory;

    protected $table = 'order';
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'widget_id',
        'created_at',
        'payment_status',
        'updated_at',
        'payment_token',
        'payment_data',
        'type',
        'payment',
        'tracking_number',
        'promo_code_id'
    ];

    protected $casts = [
        'id' => 'string',
//        'payment_data' => 'json',
        'type' => OrderTypeEnum::class,
        'payment' => OrderPayment::class,
    ];

    /**
     * The "booted" method of the model.
     *
     * @return void
     */
    protected static function booted()
    {
        static::creating(function ($model) {
            if (!$model->getKey()) {
                $model->{$model->getKeyName()} = (string) Str::uuid();
            }
        });
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class, 'widget_order_id', 'id');
    }

    public function widget(): BelongsTo
    {
        return $this->belongsTo(Widget::class);
    }

    public function getTotalSum(): int
    {
        return $this->orderItems->sum(function ($item) {
            return $item->quantity * $item->amount;
        });
    }

    /**
     * Проверяем был ли получен callback от yookassa по статусу payment_status
     *
     * @return bool
     */
    public function wasReceivedCallback(): bool
    {
        return OrderPaymentStatusEnum::wasReceivedCallback($this->payment_status);
    }
}
