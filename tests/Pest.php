<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Sleep;
use Illuminate\Support\Str;
use Saloon\Config;
use Saloon\Http\Faking\MockClient;
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
    ->afterEach(function (): void {
        MockClient::destroyGlobal();
    })
    ->in('Feature', 'Unit');

/**
 * @param  array<string, mixed>  $overrides
 * @return array<string, mixed>
 */
function pixelforgeGeneration(array $overrides = []): array
{
    return array_replace([
        'id' => 'gen_01JC2Z8Q4N6V0M3H7X5T9R2B1K',
        'object' => 'generation',
        'status' => 'queued',
        'prompt' => 'A lighthouse on a cliff at dawn, oil painting',
        'size' => '1536x1024',
        'quality' => 'high',
        'reference_images' => [],
        'metadata' => [],
        'image_url' => null,
        'image_url_expires_at' => null,
        'usage' => null,
        'rates' => pixelforgeRates(),
        'error' => null,
        'created_at' => '2026-11-04T14:03:11Z',
        'started_at' => null,
        'completed_at' => null,
    ], $overrides);
}

/**
 * @return array<string, mixed>
 */
function pixelforgeError(string $code, string $type = 'rate_limit_error'): array
{
    return ['error' => ['type' => $type, 'code' => $code, 'message' => "Pixelforge says {$code}."]];
}

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
