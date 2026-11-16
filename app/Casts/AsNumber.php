<?php

declare(strict_types=1);

namespace App\Casts;

use App\Support\Decimal;
use BcMath\Number;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;

/**
 * @implements CastsAttributes<Number|null, Number|numeric-string|int|null>
 */
final class AsNumber implements CastsAttributes
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    public function get(Model $model, string $key, mixed $value, array $attributes): ?Number
    {
        return $value === null ? null : Decimal::from($value);
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function set(Model $model, string $key, mixed $value, array $attributes): ?string
    {
        return $value === null ? null : (string) Decimal::from($value);
    }
}
