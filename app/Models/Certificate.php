<?php

namespace App\Models;

use App\Contracts\Models\CertificateInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Certificate extends Model implements CertificateInterface
{
    use HasFactory;

    protected $table = 'certificate';
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'order_item_id', 'serial', 'expire_at', 'created_at', 'amount',
        'pdf_generated_at', 'delivered_at', 'cover_path', 'pin', 'nominal',
        'currency', 'barcode',
    ];

    protected $casts = [
        'expire_at' => 'datetime:Y-m-d H:i:s',
    ];

    public function orderItem(): BelongsTo
    {
        return $this->belongsTo(OrderItem::class);
    }
}
