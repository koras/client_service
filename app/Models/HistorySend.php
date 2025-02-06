<?php

namespace App\Models;

use App\Contracts\Models\HistorySendInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HistorySend extends Model implements HistorySendInterface
{
    use HasFactory;

    const CREATED_AT = 'timestamp';
    protected $table = 'history_send';
    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'order_id', 'timestamp', 'status', 'client_id', 'recipient',
        'type', 'order_item_id', 'updated_at', 'send_type', 'external_id',
        'service_status', 'certificate_id',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function orderItem(): BelongsTo
    {
        return $this->belongsTo(OrderItem::class);
    }
}
