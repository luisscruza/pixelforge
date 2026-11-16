<?php

declare(strict_types=1);

namespace App\Actions;

use App\Support\Decimal;
use BcMath\Number;
use Illuminate\Container\Attributes\Config;

final readonly class ApplyMarkup
{
    public function __construct(
        #[Config('services.pixelforge.markup')]
        private string $markup,
    ) {
        //
    }

    public function handle(Number $providerCost): Number
    {
        return $providerCost->mul(Decimal::from($this->markup), 12);
    }
}
