<?php

declare(strict_types=1);

use App\Data\GenerationInput;
use App\Enums\GenerationQuality;
use App\Enums\GenerationSize;
use App\Enums\GenerationStatus;
use App\Http\Integrations\Pixelforge\PixelforgeConnector;
use App\Http\Integrations\Pixelforge\PixelforgeException;
use App\Http\Integrations\Pixelforge\Requests\CreateGenerationRequest;
use App\Http\Integrations\Pixelforge\Requests\GetGenerationRequest;
use App\Http\Integrations\Pixelforge\Requests\GetUsageRequest;
use Carbon\CarbonImmutable;
use Saloon\Http\Faking\MockResponse;
use Saloon\Laravel\Facades\Saloon;

it('creates a generation with the idempotency key and metadata', function (): void {
    Saloon::fake([CreateGenerationRequest::class => MockResponse::make(pixelforgeGeneration([
        'metadata' => ['generation_uuid' => 'abc'],
    ]), 202)]);

    $generation = resolve(PixelforgeConnector::class)->createGeneration(
        new GenerationInput('A lighthouse', GenerationSize::Landscape, GenerationQuality::High, ['https://example.com/style.png']),
        idempotencyKey: 'abc',
        metadata: ['generation_uuid' => 'abc'],
    );

    expect($generation->id)->toBe('gen_01JC2Z8Q4N6V0M3H7X5T9R2B1K')
        ->and($generation->status)->toBe(GenerationStatus::Queued)
        ->and($generation->usage)->toBeNull()
        ->and($generation->metadataValue('generation_uuid'))->toBe('abc');

    Saloon::assertSent(fn (CreateGenerationRequest $request, $response): bool => $response->getPendingRequest()->headers()->get('Idempotency-Key') === 'abc'
        && $response->getPendingRequest()->headers()->get('Authorization') === 'Bearer test-api-key'
        && $response->getPendingRequest()->body()?->all() === [
            'prompt' => 'A lighthouse',
            'size' => '1536x1024',
            'quality' => 'high',
            'reference_images' => ['https://example.com/style.png'],
            'metadata' => ['generation_uuid' => 'abc'],
        ]);
});

it('sends empty metadata as a json object', function (): void {
    Saloon::fake([CreateGenerationRequest::class => MockResponse::make(pixelforgeGeneration(), 202)]);

    resolve(PixelforgeConnector::class)->createGeneration(
        new GenerationInput('A lighthouse', GenerationSize::Square, GenerationQuality::Low),
        idempotencyKey: 'abc',
    );

    Saloon::assertSent(fn (CreateGenerationRequest $request, $response): bool => str_contains((string) $response->getPendingRequest()->body(), '"metadata":{}'));
});

it('reads usage, image and timestamps from a succeeded generation', function (): void {
    Saloon::fake([GetGenerationRequest::class => MockResponse::make(pixelforgeGeneration([
        'status' => 'succeeded',
        'image_url' => 'https://pixelforge.promptedit.dev/images/gen_1.png',
        'image_url_expires_at' => '2026-11-05T14:03:29Z',
        'usage' => ['input_text_tokens' => 12, 'input_image_tokens' => 765, 'output_tokens' => 6450, 'reasoning_tokens' => 3184, 'total_tokens' => 10411],
        'started_at' => '2026-11-04T14:03:13Z',
        'completed_at' => '2026-11-04T14:03:29Z',
    ]))]);

    $generation = resolve(PixelforgeConnector::class)->generation('gen_01JC2Z8Q4N6V0M3H7X5T9R2B1K');

    expect($generation->status)->toBe(GenerationStatus::Succeeded)
        ->and($generation->usage?->total())->toBe(10411)
        ->and($generation->imageUrl)->toBe('https://pixelforge.promptedit.dev/images/gen_1.png')
        ->and($generation->completedAt?->toIso8601ZuluString())->toBe('2026-11-04T14:03:29Z');
});

it('reads the error of a failed generation', function (): void {
    Saloon::fake([GetGenerationRequest::class => MockResponse::make(pixelforgeGeneration([
        'status' => 'failed',
        'error' => ['code' => 'internal_error', 'message' => 'Something went wrong on our side.'],
    ]))]);

    $generation = resolve(PixelforgeConnector::class)->generation('gen_01JC2Z8Q4N6V0M3H7X5T9R2B1K');

    expect($generation->status)->toBe(GenerationStatus::Failed)
        ->and($generation->errorCode)->toBe('internal_error')
        ->and($generation->usage)->toBeNull();
});

it('turns pixelforge errors into typed exceptions', function (int $status, string $code, ?string $retryAfter, bool $retryable): void {
    Saloon::fake([CreateGenerationRequest::class => MockResponse::make(
        pixelforgeError($code),
        $status,
        $retryAfter === null ? [] : ['Retry-After' => $retryAfter],
    )]);

    try {
        resolve(PixelforgeConnector::class)->createGeneration(
            new GenerationInput('A lighthouse', GenerationSize::Square, GenerationQuality::Low),
            idempotencyKey: 'abc',
        );
        $this->fail('Expected a PixelforgeException.');
    } catch (PixelforgeException $pixelforgeException) {
        expect($pixelforgeException->errorCode)->toBe($code)
            ->and($pixelforgeException->retryAfterSeconds)->toBe($retryAfter === null ? null : (int) $retryAfter)
            ->and($pixelforgeException->isRetryable())->toBe($retryable);
    }
})->with([
    'rate limited' => [429, 'rate_limit_exceeded', '12', true],
    'too many in flight' => [429, 'concurrency_limit_exceeded', '5', true],
    'overloaded' => [503, 'overloaded', '2', true],
    'daily quota used' => [429, 'daily_limit_exceeded', null, false],
    'prompt blocked' => [400, 'content_policy_violation', null, false],
    'invalid key' => [401, 'invalid_api_key', null, false],
    'idempotency clash' => [409, 'idempotency_key_reused', null, false],
]);

it('falls back to the http status when the error body is not json', function (): void {
    Saloon::fake([GetGenerationRequest::class => MockResponse::make('Bad Gateway', 502)]);

    try {
        resolve(PixelforgeConnector::class)->generation('gen_1');
        $this->fail('Expected a PixelforgeException.');
    } catch (PixelforgeException $pixelforgeException) {
        expect($pixelforgeException->errorCode)->toBe('http_502')
            ->and($pixelforgeException->isRetryable())->toBeTrue();
    }
});

it('reads the daily usage report for a date range', function (): void {
    Saloon::fake([GetUsageRequest::class => MockResponse::make([
        'object' => 'usage',
        'data' => [
            ['date' => '2026-11-01', 'generations' => 3, 'input_text_tokens' => 41, 'input_image_tokens' => 0, 'output_tokens' => 3300, 'reasoning_tokens' => 2950, 'cost_usd' => '0.169080000'],
            ['date' => '2026-11-02', 'generations' => 0, 'input_text_tokens' => 0, 'input_image_tokens' => 0, 'output_tokens' => 0, 'reasoning_tokens' => 0, 'cost_usd' => '0.000000000'],
        ],
    ])]);

    $days = resolve(PixelforgeConnector::class)->usage(CarbonImmutable::parse('2026-11-01'), CarbonImmutable::parse('2026-11-02'));

    expect($days)->toHaveCount(2)
        ->and($days[0]->date)->toBe('2026-11-01')
        ->and($days[0]->generations)->toBe(3)
        ->and((string) $days[0]->cost)->toBe('0.169080000');

    Saloon::assertSent(fn (GetUsageRequest $request, $response): bool => $response->getPendingRequest()->query()->all() === ['start_date' => '2026-11-01', 'end_date' => '2026-11-02']);
});
