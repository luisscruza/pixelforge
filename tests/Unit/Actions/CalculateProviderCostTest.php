<?php

declare(strict_types=1);

use App\Actions\CalculateProviderCost;
use App\Data\Rates;
use App\Data\TokenUsage;

it('matches the worst case worked example from the pixelforge docs', function (): void {
    $cost = resolve(CalculateProviderCost::class)->handle(
        new TokenUsage(inputText: 115, inputImage: 1530, output: 6450, reasoning: 8000),
        Rates::fromArray(pixelforgeRates()),
    );

    expect($cost->compare('0.373875'))->toBe(0);
});

it('keeps fractions of a cent exactly', function (): void {
    $cost = resolve(CalculateProviderCost::class)->handle(
        new TokenUsage(inputText: 1, inputImage: 0, output: 0, reasoning: 0),
        Rates::fromArray(pixelforgeRates()),
    );

    expect((string) $cost)->toBe('0.000005000000');
});

it('reconciles with a usage report day', function (): void {
    $cost = resolve(CalculateProviderCost::class)->handle(
        new TokenUsage(inputText: 41, inputImage: 0, output: 3300, reasoning: 2950),
        Rates::fromArray(pixelforgeRates()),
    );

    expect($cost->compare('0.169080000'))->toBe(0);
});

it('uses the rates it is given rather than current pricing', function (): void {
    $cost = resolve(CalculateProviderCost::class)->handle(
        new TokenUsage(inputText: 0, inputImage: 0, output: 1_000_000, reasoning: 0),
        Rates::fromArray([...pixelforgeRates(), 'output_tokens' => '32.00']),
    );

    expect($cost->compare('32'))->toBe(0);
});
