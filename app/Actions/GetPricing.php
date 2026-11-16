<?php

declare(strict_types=1);

namespace App\Actions;

use App\Data\PricingData;
use App\Http\Integrations\Pixelforge\PixelforgeConnector;
use Illuminate\Contracts\Cache\Repository;

final readonly class GetPricing
{
    private const string CACHE_KEY = 'pixelforge.pricing';

    private const int CACHE_SECONDS = 300;

    public function __construct(
        private PixelforgeConnector $pixelforge,
        private Repository $cache,
    ) {
        //
    }

    public function handle(): PricingData
    {
        return PricingData::fromArray($this->cache->remember(
            self::CACHE_KEY,
            self::CACHE_SECONDS,
            fn (): array => $this->pixelforge->pricing()->toArray(),
        ));
    }
}
