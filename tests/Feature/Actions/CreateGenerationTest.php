<?php

declare(strict_types=1);

use App\Actions\CreateGeneration;
use App\Actions\GetCreditBalance;
use App\Data\CostEstimate;
use App\Data\GenerationInput;
use App\Enums\CreditTransactionType;
use App\Enums\GenerationQuality;
use App\Enums\GenerationSize;
use App\Enums\GenerationStatus;
use App\Exceptions\InsufficientCreditsException;
use App\Jobs\SubmitGenerationJob;
use App\Models\Generation;
use App\Models\User;
use BcMath\Number;
use Illuminate\Support\Facades\Queue;

function estimate(string $maximumPrice = '0.4625'): CostEstimate
{
    return new CostEstimate(new Number('0.3'), new Number('0.37'), new Number('0.375'), new Number($maximumPrice));
}

function input(): GenerationInput
{
    return new GenerationInput('A lighthouse', GenerationSize::Portrait, GenerationQuality::High, ['https://example.com/style.png']);
}

it('stores a pending generation and queues its submission', function (): void {
    Queue::fake();
    $user = User::factory()->withCredits('1')->create();

    $generation = resolve(CreateGeneration::class)->handle($user, input(), estimate());

    expect($generation->user_id)->toBe($user->id)
        ->and($generation->status)->toBe(GenerationStatus::Pending)
        ->and($generation->reference_images)->toBe(['https://example.com/style.png'])
        ->and((string) $generation->refresh()->estimated_price)->toBe('0.375000000000')
        ->and((string) $generation->maximum_price)->toBe('0.462500000000');

    Queue::assertPushed(SubmitGenerationJob::class, fn (SubmitGenerationJob $job): bool => $job->generation->is($generation));
});

it('holds the maximum price until the generation finishes', function (): void {
    Queue::fake();
    $user = User::factory()->withCredits('1')->create();

    $generation = resolve(CreateGeneration::class)->handle($user, input(), estimate());

    expect((string) resolve(GetCreditBalance::class)->handle($user))->toBe('0.537500000000')
        ->and($generation->creditTransactions()->sole()->type)->toBe(CreditTransactionType::Hold);
});

it('allows spending the balance down to exactly zero', function (): void {
    Queue::fake();
    $user = User::factory()->withCredits('0.4625')->create();

    resolve(CreateGeneration::class)->handle($user, input(), estimate());

    expect(resolve(GetCreditBalance::class)->handle($user)->compare(0))->toBe(0);
});

it('refuses a generation the user cannot afford at its maximum price', function (): void {
    Queue::fake();
    $user = User::factory()->withCredits('0.46')->create();

    expect(fn () => resolve(CreateGeneration::class)->handle($user, input(), estimate()))
        ->toThrow(InsufficientCreditsException::class);

    expect(Generation::query()->count())->toBe(0)
        ->and((string) resolve(GetCreditBalance::class)->handle($user))->toBe('0.460000000000');
    Queue::assertNothingPushed();
});
