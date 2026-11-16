<?php

declare(strict_types=1);

namespace App\Actions;

use App\Data\PricingData;
use App\Http\Integrations\Pixelforge\PixelforgeConnector;
use Illuminate\Contracts\Cache\Repository;

final readonly class GetPricing
{
    private const int CACHE_SECONDS = 300;

    public function __construct(
        private PixelforgeConnector $pixelforge,
        private Repository $cache,
    ) {
        //
    }

    public function handle(): PricingData
    {
        return $this->cache->remember(
            'pixelforge.pricing',
            self::CACHE_SECONDS,
            fn (): PricingData => $this->pixelforge->pricing(),
        );
    }
}
