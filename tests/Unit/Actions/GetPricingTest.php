<?php

declare(strict_types=1);

use App\Actions\GetPricing;
use App\Enums\GenerationQuality;
use App\Enums\GenerationSize;
use App\Http\Integrations\Pixelforge\Requests\GetPricingRequest;
use Saloon\Http\Faking\MockResponse;
use Saloon\Laravel\Facades\Saloon;

it('reads live pricing from pixelforge', function (): void {
    Saloon::fake([GetPricingRequest::class => MockResponse::make([
        ...pixelforgePricing(),
        'rates' => [...pixelforgeRates(), 'output_tokens' => '32.00'],
    ])]);

    $pricing = resolve(GetPricing::class)->handle();

    expect($pricing->rates->output->compare('32'))->toBe(0)
        ->and($pricing->outputTokens(GenerationQuality::High, GenerationSize::Portrait))->toBe(6450)
        ->and($pricing->maximumReasoningTokens(GenerationQuality::Medium))->toBe(2000);
});

it('caches pricing so estimates do not spend the rate limit', function (): void {
    Saloon::fake([GetPricingRequest::class => MockResponse::make(pixelforgePricing())]);

    resolve(GetPricing::class)->handle();
    resolve(GetPricing::class)->handle();

    Saloon::assertSentCount(1);
});

it('refreshes pricing once the cache expires', function (): void {
    Saloon::fake([GetPricingRequest::class => MockResponse::make(pixelforgePricing())]);

    resolve(GetPricing::class)->handle();
    $this->travel(301)->seconds();
    resolve(GetPricing::class)->handle();

    Saloon::assertSentCount(2);
});
