<?php

namespace App\Models;

use App\Casts\OrderPayment;
use App\Contracts\Models\LifeCycleInterface;
use App\Enums\OrderPaymentStatusEnum;
use App\Enums\OrderTypeEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class LifeCycle extends Model implements LifeCycleInterface
{
    use HasFactory;
    // http://samara.local/api/life_cycle?order_id=123123&system=asdfasdfasdf&status=33&value=fasdfadsg
    protected $table = 'life_cycle';
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'order_id', // номер заказа
        'status', // статус текущего заказа
        'system', // система которая отправила лог
        'value',// значение для понимания
        'created_at',
        'updated_at',
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
