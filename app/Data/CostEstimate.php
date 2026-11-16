<?php

declare(strict_types=1);

namespace App\Data;

use BcMath\Number;

final readonly class CostEstimate
{
    public function __construct(
        public Number $expectedProviderCost,
        public Number $maximumProviderCost,
        public Number $expectedPrice,
        public Number $maximumPrice,
    ) {
        //
    }

    /**
     * @return array{expected_price: string, maximum_price: string}
     */
    public function toArray(): array
    {
        return [
            'expected_price' => (string) $this->expectedPrice,
            'maximum_price' => (string) $this->maximumPrice,
        ];
    }
}
