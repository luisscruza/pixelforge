<?php

declare(strict_types=1);

use App\Actions\SubmitGeneration;
use App\Enums\GenerationStatus;
use App\Http\Integrations\Pixelforge\Requests\CreateGenerationRequest;
use App\Jobs\SubmitGenerationJob;
use App\Models\Generation;
use Saloon\Exceptions\Request\FatalRequestException;
use Saloon\Http\Faking\MockResponse;
use Saloon\Http\PendingRequest;
use Saloon\Laravel\Facades\Saloon;

it('waits as long as retry-after asks before trying again', function (string $code, int $status, string $retryAfter, int $expectedDelay): void {
    $generation = Generation::factory()->create();
    Saloon::fake([CreateGenerationRequest::class => MockResponse::make(pixelforgeError($code), $status, ['Retry-After' => $retryAfter])]);

    $job = new SubmitGenerationJob($generation)->withFakeQueueInteractions();
    $job->handle(resolve(SubmitGeneration::class));

    $job->assertReleased(delay: $expectedDelay);

    expect($generation->refresh()->status)->toBe(GenerationStatus::Pending);
})->with([
    'rate limited' => ['rate_limit_exceeded', 429, '12', 12],
    'too many in flight' => ['concurrency_limit_exceeded', 429, '5', 5],
    'overloaded uses the backoff floor' => ['overloaded', 503, '1', 2],
]);

it('retries when pixelforge cannot be reached at all', function (): void {
    $generation = Generation::factory()->create();
    Saloon::fake([CreateGenerationRequest::class => MockResponse::make()->throw(
        fn (PendingRequest $request): FatalRequestException => new FatalRequestException(new RuntimeException('Connection timed out'), $request),
    )]);

    $job = new SubmitGenerationJob($generation)->withFakeQueueInteractions();
    $job->handle(resolve(SubmitGeneration::class));

    $job->assertReleased(delay: 2);
});

it('gives up after the retry window without charging the user', function (): void {
    $generation = Generation::factory()->create();

    new SubmitGenerationJob($generation)->failed(new RuntimeException('Max attempts'));

    $generation->refresh();

    expect($generation->status)->toBe(GenerationStatus::Failed)
        ->and($generation->error_code)->toBe('submission_failed')
        ->and($generation->price?->compare(0))->toBe(0);
});

it('does not overwrite a generation that finished while the job was giving up', function (): void {
    $generation = Generation::factory()->succeeded()->create();

    new SubmitGenerationJob($generation)->failed(new RuntimeException('Max attempts'));

    expect($generation->refresh()->status)->toBe(GenerationStatus::Succeeded);
});

it('keeps retrying for ten minutes', function (): void {
    $job = new SubmitGenerationJob(Generation::factory()->create());

    expect($job->retryUntil()->getTimestamp())->toBe(now()->addMinutes(10)->getTimestamp());
});
