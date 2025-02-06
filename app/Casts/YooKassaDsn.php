<?php

namespace App\Casts;

use App\ValueObjects\YooKassaDsnObj;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;

class YooKassaDsn implements CastsAttributes
{
    /**
     * Cast the given value.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function get(Model $model, string $key, mixed $value, array $attributes): ?YooKassaDsnObj
    {
        return YooKassaDsnObj::fromWidgetAttribute($attributes['ykassa_dsn']);
    }

    /**
     * Prepare the given value for storage.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function set(Model $model, string $key, mixed $value, array $attributes): string
    {
        if (!$value instanceof YooKassaDsnObj) {
            throw new InvalidArgumentException('The given value is not an YooKassaDsnObj instance.');
        }

        return $value->__toString();
    }
}
