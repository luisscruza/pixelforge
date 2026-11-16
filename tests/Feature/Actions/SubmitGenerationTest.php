<?php

declare(strict_types=1);

use App\Actions\SubmitGeneration;
use App\Enums\GenerationStatus;
use App\Http\Integrations\Pixelforge\PixelforgeException;
use App\Http\Integrations\Pixelforge\Requests\CreateGenerationRequest;
use App\Models\Generation;
use Saloon\Http\Faking\MockResponse;
use Saloon\Laravel\Facades\Saloon;

it('submits with our uuid as idempotency key and records the pixelforge id', function (): void {
    $generation = Generation::factory()->create();
    Saloon::fake([CreateGenerationRequest::class => MockResponse::make(pixelforgeGeneration([
        'id' => 'gen_NEW',
        'metadata' => ['generation_uuid' => $generation->uuid],
    ]), 202)]);

    resolve(SubmitGeneration::class)->handle($generation);

    $generation->refresh();

    expect($generation->pixelforge_id)->toBe('gen_NEW')
        ->and($generation->status)->toBe(GenerationStatus::Queued)
        ->and($generation->submission_attempts)->toBe(1)
        ->and($generation->submitted_at)->not->toBeNull();

    Saloon::assertSent(fn (CreateGenerationRequest $request, $response): bool => $response->getPendingRequest()->headers()->get('Idempotency-Key') === $generation->uuid);
});

it('does not submit a generation that already left pending', function (): void {
    Saloon::fake([]);
    $generation = Generation::factory()->queued()->create();

    resolve(SubmitGeneration::class)->handle($generation);

    Saloon::assertNothingSent();
});

it('accepts a replayed submission whose generation already moved on', function (): void {
    $generation = Generation::factory()->create();
    Saloon::fake([CreateGenerationRequest::class => MockResponse::make(pixelforgeGeneration([
        'status' => 'processing',
        'metadata' => ['generation_uuid' => $generation->uuid],
    ]), 202, ['Idempotent-Replayed' => 'true'])]);

    resolve(SubmitGeneration::class)->handle($generation);

    expect($generation->refresh()->status)->toBe(GenerationStatus::Processing);
});

it('fails without charging when pixelforge refuses the request', function (int $status, string $code): void {
    $generation = Generation::factory()->create();
    Saloon::fake([CreateGenerationRequest::class => MockResponse::make(pixelforgeError($code, 'invalid_request_error'), $status)]);

    resolve(SubmitGeneration::class)->handle($generation);

    $generation->refresh();

    expect($generation->status)->toBe(GenerationStatus::Failed)
        ->and($generation->error_code)->toBe($code)
        ->and($generation->price?->compare(0))->toBe(0);
})->with([
    'blocked prompt' => [400, 'content_policy_violation'],
    'daily quota' => [429, 'daily_limit_exceeded'],
]);

it('leaves the generation pending and rethrows when the error is worth retrying', function (): void {
    $generation = Generation::factory()->create();
    Saloon::fake([CreateGenerationRequest::class => MockResponse::make(pixelforgeError('overloaded', 'api_error'), 503, ['Retry-After' => '2'])]);

    expect(fn () => resolve(SubmitGeneration::class)->handle($generation))->toThrow(PixelforgeException::class);

    expect($generation->refresh()->status)->toBe(GenerationStatus::Pending)
        ->and($generation->submission_attempts)->toBe(1);
});
