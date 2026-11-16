<?php

declare(strict_types=1);

use App\Actions\EstimateGenerationCost;
use App\Data\GenerationInput;
use App\Data\PricingData;
use App\Enums\GenerationQuality;
use App\Enums\GenerationSize;
use BcMath\Number;

it('bounds the maximum by the worst case from the docs', function (): void {
    $estimate = resolve(EstimateGenerationCost::class)->handle(
        new GenerationInput(
            prompt: str_repeat('a', 400),
            size: GenerationSize::Landscape,
            quality: GenerationQuality::High,
            referenceImages: ['https://example.com/a.png', 'https://example.com/b.png'],
        ),
        PricingData::fromArray(pixelforgePricing()),
    );

    expect($estimate->maximumProviderCost->compare('0.373875'))->toBe(0)
        ->and($estimate->maximumPrice->compare('0.46734375'))->toBe(0);
});

it('expects the midpoint of the reasoning range', function (): void {
    $estimate = resolve(EstimateGenerationCost::class)->handle(
        new GenerationInput(
            prompt: str_repeat('a', 400),
            size: GenerationSize::Landscape,
            quality: GenerationQuality::High,
            referenceImages: ['https://example.com/a.png', 'https://example.com/b.png'],
        ),
        PricingData::fromArray(pixelforgePricing()),
    );

    expect($estimate->expectedProviderCost->compare('0.33005'))->toBe(0)
        ->and($estimate->expectedPrice->compare('0.4125625'))->toBe(0);
});

it('counts unicode characters rather than bytes', function (): void {
    $estimate = resolve(EstimateGenerationCost::class)->handle(
        new GenerationInput(prompt: 'ñññññ', size: GenerationSize::Square, quality: GenerationQuality::Low),
        PricingData::fromArray(pixelforgePricing()),
    );

    expect($estimate->expectedProviderCost->compare('0.014335'))->toBe(0);
});

it('charges reference images per image', function (GenerationQuality $quality, int $images): void {
    $pricing = PricingData::fromArray(pixelforgePricing());
    $action = resolve(EstimateGenerationCost::class);

    $without = $action->handle(new GenerationInput('a cat', GenerationSize::Square, $quality), $pricing);
    $with = $action->handle(
        new GenerationInput('a cat', GenerationSize::Square, $quality, array_fill(0, $images, 'https://example.com/a.png')),
        $pricing,
    );

    expect(($with->expectedProviderCost - $without->expectedProviderCost)->compare((new Number('0.00765'))->mul($images)))->toBe(0);
})->with([
    [GenerationQuality::Low, 1],
    [GenerationQuality::Medium, 4],
]);
