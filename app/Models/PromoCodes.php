<?php

namespace App\Models;

use App\Casts\OrderPayment;
use App\Contracts\Models\LifeCycleInterface;
use App\Contracts\Models\PromoCodesInterface;
use App\Enums\OrderPaymentStatusEnum;
use App\Enums\OrderTypeEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class PromoCodes extends Model implements PromoCodesInterface
{
    use HasFactory;
    protected $table = 'promo_codes';
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'code',
        'product',
        'price',
        'needProduct',
        'status',
        'type',
        'start',
        'finish'
    ];

    /**
     * The "booted" method of the model.
     *
     * @return void
     */
    protected static function booted()
    {

    }

}
