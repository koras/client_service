<?php

namespace App\Casts;

use App\Contracts\Models\WidgetInterface;
use App\ValueObjects\WidgetSaleLabelObj;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;

class WidgetSaleLabel implements CastsAttributes
{
    /**
     * Cast the given value.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function get(Model $model, string $key, mixed $value, array $attributes): ?WidgetSaleLabelObj
    {
        if (!$model instanceof WidgetInterface) {
            throw new InvalidArgumentException('The given model is not an WidgetInterface instance.');
        }

        return WidgetSaleLabelObj::fromWidget($model);
    }

    /**
     * Prepare the given value for storage.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function set(Model $model, string $key, mixed $value, array $attributes): array
    {
        if (!$value instanceof WidgetSaleLabelObj) {
            throw new InvalidArgumentException('The given value is not an WidgetSaleLabelObj instance.');
        }

        return $value->toArray();
    }
}
