<?php

namespace App\Casts;

use App\ValueObjects\OrderPaymentObj;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;

class OrderPayment implements CastsAttributes
{
    /**
     * Cast the given value.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function get(Model $model, string $key, mixed $value, array $attributes): mixed
    {
        return new OrderPaymentObj(
            $attributes['payment_status'],
            $attributes['payment_token'],
            json_decode($attributes['payment_data'], true),
        );
    }

    /**
     * Prepare the given value for storage.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function set(Model $model, string $key, mixed $value, array $attributes): array
    {
        if (!$value instanceof OrderPaymentObj) {
            throw new InvalidArgumentException('The given value is not an OrderPaymentObj instance.');
        }

        return $value->toArray();
    }
}
