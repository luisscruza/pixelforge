<?php

declare(strict_types=1);

use App\Actions\GetPricing;
use App\Enums\GenerationQuality;
use App\Enums\GenerationSize;
use App\Http\Integrations\Pixelforge\Requests\GetPricingRequest;
use Illuminate\Support\Facades\Cache;
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

it('survives a cache store that refuses to unserialize objects', function (): void {
    config(['cache.default' => 'file', 'cache.serializable_classes' => false]);
    Cache::store('file')->forget('pixelforge.pricing');
    Saloon::fake([GetPricingRequest::class => MockResponse::make(pixelforgePricing())]);

    resolve(GetPricing::class)->handle();
    $cached = resolve(GetPricing::class)->handle();

    expect($cached->rates->output->compare('40'))->toBe(0)
        ->and($cached->outputTokens(GenerationQuality::Low, GenerationSize::Square))->toBe(280);
    Saloon::assertSentCount(1);

    Cache::store('file')->forget('pixelforge.pricing');
});
