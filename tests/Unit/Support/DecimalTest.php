<?php

declare(strict_types=1);

use App\Support\Decimal;

it('parses numeric values from api payloads exactly', function (mixed $value, string $expected): void {
    expect((string) Decimal::from($value))->toBe($expected);
})->with([
    'decimal string' => ['0.046125000', '0.046125000'],
    'integer' => [3, '3'],
    'json float' => [0.15, '0.15'],
]);

it('rejects values that are not numbers', function (mixed $value): void {
    Decimal::from($value);
})->with([
    'text' => ['free'],
    'null' => [null],
    'array' => [[]],
])->throws(UnexpectedValueException::class);
