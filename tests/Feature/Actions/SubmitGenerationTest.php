<?php

declare(strict_types=1);

use App\Actions\ApplyGenerationSnapshot;
use App\Actions\SubmitGeneration;
use App\Data\GenerationData;
use App\Enums\GenerationStatus;
use App\Enums\SubmissionStatus;
use App\Http\Integrations\Pixelforge\PixelforgeException;
use App\Http\Integrations\Pixelforge\Requests\CreateGenerationRequest;
use App\Models\Generation;
use Saloon\Exceptions\Request\FatalRequestException;
use Saloon\Http\Faking\MockResponse;
use Saloon\Http\PendingRequest;
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

it('marks the outcome unknown when the request times out', function (): void {
    $generation = Generation::factory()->create();
    Saloon::fake([CreateGenerationRequest::class => MockResponse::make()->throw(
        fn (PendingRequest $request): FatalRequestException => new FatalRequestException(new RuntimeException('Timed out'), $request),
    )]);

    expect(fn () => resolve(SubmitGeneration::class)->handle($generation))->toThrow(FatalRequestException::class);

    expect($generation->refresh()->status)->toBe(GenerationStatus::Pending)
        ->and($generation->submission_status)->toBe(SubmissionStatus::Unknown);
});

it('treats a 5xx without a pixelforge error code as an unknown outcome', function (): void {
    $generation = Generation::factory()->create();
    Saloon::fake([CreateGenerationRequest::class => MockResponse::make('Bad Gateway', 502)]);

    expect(fn () => resolve(SubmitGeneration::class)->handle($generation))->toThrow(PixelforgeException::class);

    expect($generation->refresh()->submission_status)->toBe(SubmissionStatus::Unknown);
});

it('keeps submitting when pixelforge answered that it created nothing', function (): void {
    $generation = Generation::factory()->create();
    Saloon::fake([CreateGenerationRequest::class => MockResponse::make(pixelforgeError('overloaded', 'api_error'), 503, ['Retry-After' => '2'])]);

    expect(fn () => resolve(SubmitGeneration::class)->handle($generation))->toThrow(PixelforgeException::class);

    expect($generation->refresh()->submission_status)->toBe(SubmissionStatus::Submitting);
});

it('records an explicit refusal as rejected', function (): void {
    $generation = Generation::factory()->create();
    Saloon::fake([CreateGenerationRequest::class => MockResponse::make(pixelforgeError('content_policy_violation', 'invalid_request_error'), 400)]);

    resolve(SubmitGeneration::class)->handle($generation);

    expect($generation->refresh()->submission_status)->toBe(SubmissionStatus::Rejected);
});

it('does not overwrite a success that landed while our request was timing out', function (): void {
    $generation = Generation::factory()->create();
    Saloon::fake([CreateGenerationRequest::class => MockResponse::make()->throw(function (PendingRequest $request) use ($generation): FatalRequestException {
        resolve(ApplyGenerationSnapshot::class)->handle(GenerationData::fromArray(pixelforgeGeneration([
            'status' => 'succeeded',
            'metadata' => ['generation_uuid' => $generation->uuid],
            'usage' => ['input_text_tokens' => 3, 'input_image_tokens' => 0, 'output_tokens' => 1100, 'reasoning_tokens' => 1000, 'total_tokens' => 2103],
        ])));

        return new FatalRequestException(new RuntimeException('Timed out'), $request);
    })]);

    expect(fn () => resolve(SubmitGeneration::class)->handle($generation))->toThrow(FatalRequestException::class);

    expect($generation->refresh()->status)->toBe(GenerationStatus::Succeeded)
        ->and($generation->submission_status)->toBe(SubmissionStatus::Accepted);
});

it('treats a successful response it cannot read as an unknown outcome', function (): void {
    $generation = Generation::factory()->create();
    Saloon::fake([CreateGenerationRequest::class => MockResponse::make(['id' => 'gen_1', 'status' => 'teleporting'], 202)]);

    expect(fn () => resolve(SubmitGeneration::class)->handle($generation))->toThrow(UnexpectedValueException::class);

    expect($generation->refresh()->submission_status)->toBe(SubmissionStatus::Unknown)
        ->and($generation->status)->toBe(GenerationStatus::Pending);
});
