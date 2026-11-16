<?php

declare(strict_types=1);

use App\Actions\CreateGeneration;
use App\Actions\GetCreditBalance;
use App\Actions\SubmitGeneration;
use App\Data\CostEstimate;
use App\Data\GenerationInput;
use App\Enums\GenerationQuality;
use App\Enums\GenerationSize;
use App\Enums\GenerationStatus;
use App\Enums\SubmissionStatus;
use App\Http\Integrations\Pixelforge\Requests\CreateGenerationRequest;
use App\Jobs\SubmitGenerationJob;
use App\Models\Generation;
use App\Models\User;
use BcMath\Number;
use Illuminate\Support\Facades\Queue;
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

it('flags the generation for reconciliation instead of failing it when retries run out', function (): void {
    $user = User::factory()->withCredits('1')->create();
    Queue::fake();
    $generation = resolve(CreateGeneration::class)->handle(
        $user,
        new GenerationInput('A lighthouse', GenerationSize::Square, GenerationQuality::Medium),
        new CostEstimate(new Number('0.06'), new Number('0.07'), new Number('0.075'), new Number('0.0875')),
    );

    new SubmitGenerationJob($generation)->failed(new RuntimeException('Max attempts'));

    $generation->refresh();

    expect($generation->status)->toBe(GenerationStatus::Pending)
        ->and($generation->submission_status)->toBe(SubmissionStatus::NeedsReconciliation)
        ->and((string) resolve(GetCreditBalance::class)->handle($user))->toBe('0.912500000000');
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
