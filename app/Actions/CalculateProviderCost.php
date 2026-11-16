<?php

declare(strict_types=1);

namespace App\Actions;

use App\Data\Rates;
use App\Data\TokenUsage;
use BcMath\Number;

final readonly class CalculateProviderCost
{
    private const int TOKENS_PER_RATE_UNIT = 1_000_000;

    private const int SCALE = 12;

    public function handle(TokenUsage $usage, Rates $rates): Number
    {
        $perRateUnit = $rates->inputText * $usage->inputText
            + $rates->inputImage * $usage->inputImage
            + $rates->output * $usage->output
            + $rates->reasoning * $usage->reasoning;

        return $perRateUnit->div(self::TOKENS_PER_RATE_UNIT, self::SCALE);
    }
}
