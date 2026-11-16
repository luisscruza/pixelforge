<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Sleep;
use Illuminate\Support\Str;
use Saloon\Config;
use Tests\TestCase;

pest()->extend(TestCase::class)
    ->use(RefreshDatabase::class)
    ->beforeEach(function (): void {
        Str::createRandomStringsNormally();
        Str::createUuidsNormally();
        Http::preventStrayRequests();
        Config::preventStrayRequests();
        Sleep::fake();

        $this->freezeTime();
    })
    ->in('Feature', 'Unit');

/**
 * @return array<string, mixed>
 */
function pixelforgePricing(): array
{
    return [
        'object' => 'pricing',
        'currency' => 'usd',
        'unit' => 'per_1m_tokens',
        'rates' => pixelforgeRates(),
        'tokens' => [
            'characters_per_text_token' => 4,
            'text_token_variance' => 0.15,
            'per_reference_image' => 765,
            'output' => [
                'low' => ['1024x1024' => 280, '1536x1024' => 420, '1024x1536' => 420],
                'medium' => ['1024x1024' => 1100, '1536x1024' => 1650, '1024x1536' => 1650],
                'high' => ['1024x1024' => 4300, '1536x1024' => 6450, '1024x1536' => 6450],
            ],
            'reasoning' => [
                'low' => ['min' => 0, 'max' => 500],
                'medium' => ['min' => 200, 'max' => 2000],
                'high' => ['min' => 1000, 'max' => 8000],
            ],
        ],
    ];
}

/**
 * @return array<string, string>
 */
function pixelforgeRates(): array
{
    return [
        'input_text_tokens' => '5.00',
        'input_image_tokens' => '10.00',
        'output_tokens' => '40.00',
        'reasoning_tokens' => '12.50',
    ];
}
