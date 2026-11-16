<?php

declare(strict_types=1);

use App\Actions\ApplyMarkup;
use BcMath\Number;

it('adds a 25% markup without losing precision', function (): void {
    $price = resolve(ApplyMarkup::class)->handle(new Number('0.373875000'));

    expect((string) $price)->toBe('0.467343750000');
});

it('reads the markup from configuration', function (): void {
    config(['services.pixelforge.markup' => '2']);

    $price = resolve(ApplyMarkup::class)->handle(new Number('0.000000001'));

    expect($price->compare('0.000000002'))->toBe(0);
});
