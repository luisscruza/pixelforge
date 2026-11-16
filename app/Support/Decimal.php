<?php

declare(strict_types=1);

namespace App\Support;

use BcMath\Number;
use UnexpectedValueException;

final class Decimal
{
    public static function from(mixed $value): Number
    {
        if ($value instanceof Number) {
            return $value;
        }

        if (is_int($value) || (is_string($value) && is_numeric($value))) {
            return new Number($value);
        }

        if (is_float($value)) {
            return self::from((string) $value);
        }

        throw new UnexpectedValueException('Expected a decimal value, got '.get_debug_type($value).'.');
    }
}
