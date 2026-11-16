<?php

declare(strict_types=1);

use App\Actions\ReconcileGenerations;
use App\Enums\GenerationStatus;
use App\Enums\SubmissionStatus;
use App\Http\Integrations\Pixelforge\Requests\CreateGenerationRequest;
use App\Http\Integrations\Pixelforge\Requests\GetGenerationRequest;
use App\Jobs\SubmitGenerationJob;
use App\Models\Generation;
use Illuminate\Support\Facades\Queue;
use Saloon\Http\Faking\MockResponse;
use Saloon\Laravel\Facades\Saloon;

it('re-queues generations stuck in the outbox', function (): void {
    Queue::fake();
    $stalled = Generation::factory()->create(['updated_at' => now()->subMinutes(3)]);
    Generation::factory()->create(['updated_at' => now()->subSeconds(30)]);

    $result = resolve(ReconcileGenerations::class)->handle();

    expect($result->resubmitted)->toBe(1);
    Queue::assertPushed(SubmitGenerationJob::class, 1);
    Queue::assertPushed(SubmitGenerationJob::class, fn (SubmitGenerationJob $job): bool => $job->generation->is($stalled));
});

it('re-queues submissions whose outcome is unknown', function (): void {
    Queue::fake();
    Generation::factory()->create(['submission_status' => SubmissionStatus::Unknown, 'updated_at' => now()->subMinutes(3)]);
    Generation::factory()->create(['submission_status' => SubmissionStatus::NeedsReconciliation, 'submitted_at' => now()->subMinutes(5), 'updated_at' => now()->subMinutes(5)]);

    expect(resolve(ReconcileGenerations::class)->handle()->resubmitted)->toBe(1);
    Queue::assertPushed(SubmitGenerationJob::class, 1);
});

it('re-posts unresolved submissions with the same key and adopts what pixelforge already has', function (): void {
    $generation = Generation::factory()->create([
        'submission_status' => SubmissionStatus::NeedsReconciliation,
        'submitted_at' => now()->subMinutes(16),
        'updated_at' => now()->subMinutes(16),
    ]);
    Saloon::fake([CreateGenerationRequest::class => MockResponse::make(pixelforgeGeneration([
        'status' => 'processing',
        'metadata' => ['generation_uuid' => $generation->uuid],
    ]), 202, ['Idempotent-Replayed' => 'true'])]);

    $result = resolve(ReconcileGenerations::class)->handle();

    $generation->refresh();

    expect($result->reconciled)->toBe(1)
        ->and($generation->status)->toBe(GenerationStatus::Processing)
        ->and($generation->submission_status)->toBe(SubmissionStatus::Accepted);
    Saloon::assertSent(fn (CreateGenerationRequest $request, $response): bool => $response->getPendingRequest()->headers()->get('Idempotency-Key') === $generation->uuid);
});

it('fails an unresolved submission only when pixelforge definitively refuses it', function (): void {
    $generation = Generation::factory()->create([
        'submission_status' => SubmissionStatus::NeedsReconciliation,
        'submitted_at' => now()->subMinutes(16),
    ]);
    Saloon::fake([CreateGenerationRequest::class => MockResponse::make(pixelforgeError('content_policy_violation', 'invalid_request_error'), 400)]);

    resolve(ReconcileGenerations::class)->handle();

    expect($generation->refresh()->status)->toBe(GenerationStatus::Failed)
        ->and($generation->submission_status)->toBe(SubmissionStatus::Rejected);
});

it('keeps an unresolved submission open while pixelforge still cannot answer', function (): void {
    $generation = Generation::factory()->create([
        'submission_status' => SubmissionStatus::NeedsReconciliation,
        'submitted_at' => now()->subMinutes(16),
    ]);
    Saloon::fake([CreateGenerationRequest::class => MockResponse::make('Bad Gateway', 502)]);

    resolve(ReconcileGenerations::class)->handle();

    expect($generation->refresh()->status)->toBe(GenerationStatus::Pending)
        ->and($generation->submission_status)->toBe(SubmissionStatus::NeedsReconciliation);
});

it('waits fifteen minutes between attempts on an unresolved submission', function (): void {
    Saloon::fake([]);
    Generation::factory()->create([
        'submission_status' => SubmissionStatus::NeedsReconciliation,
        'submitted_at' => now()->subMinutes(10),
        'updated_at' => now()->subMinutes(10),
    ]);

    expect(resolve(ReconcileGenerations::class)->handle()->reconciled)->toBe(0);
    Saloon::assertNothingSent();
});

it('polls pixelforge for generations whose webhooks never arrived', function (): void {
    $generation = Generation::factory()->processing()->create(['updated_at' => now()->subMinutes(3)]);
    Saloon::fake([GetGenerationRequest::class => MockResponse::make(pixelforgeGeneration([
        'id' => $generation->pixelforge_id,
        'status' => 'succeeded',
        'metadata' => ['generation_uuid' => $generation->uuid],
        'usage' => ['input_text_tokens' => 3, 'input_image_tokens' => 0, 'output_tokens' => 1100, 'reasoning_tokens' => 1000, 'total_tokens' => 2103],
        'completed_at' => '2026-11-04T14:03:29Z',
    ]))]);

    $result = resolve(ReconcileGenerations::class)->handle();

    expect($result->polled)->toBe(1)
        ->and($generation->refresh()->status)->toBe(GenerationStatus::Succeeded)
        ->and((string) $generation->price)->toBe('0.070643750000');
});

it('leaves recently updated and finished generations alone', function (): void {
    Saloon::fake([]);
    Generation::factory()->processing()->create(['updated_at' => now()->subSeconds(30)]);
    Generation::factory()->succeeded()->create(['updated_at' => now()->subHour()]);
    Generation::factory()->failed()->create(['updated_at' => now()->subHour()]);

    resolve(ReconcileGenerations::class)->handle();

    Saloon::assertNothingSent();
});

it('stops polling as soon as pixelforge rate limits us', function (): void {
    Generation::factory()->count(3)->processing()->create(['updated_at' => now()->subMinutes(3)]);
    Saloon::fake([GetGenerationRequest::class => MockResponse::make(pixelforgeError('rate_limit_exceeded'), 429, ['Retry-After' => '30'])]);

    $result = resolve(ReconcileGenerations::class)->handle();

    expect($result->polled)->toBe(0);
    Saloon::assertSentCount(1);
});

it('skips generations pixelforge no longer knows about and keeps going', function (): void {
    Generation::factory()->count(2)->processing()->create(['updated_at' => now()->subMinutes(3)]);
    Saloon::fake([GetGenerationRequest::class => MockResponse::make(pixelforgeError('not_found', 'invalid_request_error'), 404)]);

    resolve(ReconcileGenerations::class)->handle();

    Saloon::assertSentCount(2);
});
