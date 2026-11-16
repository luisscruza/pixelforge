<?php

declare(strict_types=1);

use App\Actions\ApplyGenerationSnapshot;
use App\Actions\CreateGeneration;
use App\Actions\GetCreditBalance;
use App\Actions\MarkGenerationAsFailed;
use App\Data\CostEstimate;
use App\Data\GenerationData;
use App\Data\GenerationInput;
use App\Enums\CreditTransactionType;
use App\Enums\GenerationQuality;
use App\Enums\GenerationSize;
use App\Models\Generation;
use App\Models\User;
use BcMath\Number;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\Queue;

function heldGeneration(User $user): Generation
{
    Queue::fake();

    return resolve(CreateGeneration::class)->handle(
        $user,
        new GenerationInput('A lighthouse', GenerationSize::Square, GenerationQuality::Medium),
        new CostEstimate(new Number('0.06'), new Number('0.07'), new Number('0.075'), new Number('0.0875')),
    );
}

function pixelforgeSays(Generation $generation, array $overrides): GenerationData
{
    return GenerationData::fromArray(pixelforgeGeneration([
        'id' => 'gen_'.$generation->id,
        'metadata' => ['generation_uuid' => $generation->uuid],
        ...$overrides,
    ]));
}

function successFor(Generation $generation): GenerationData
{
    return pixelforgeSays($generation, [
        'status' => 'succeeded',
        'usage' => ['input_text_tokens' => 3, 'input_image_tokens' => 0, 'output_tokens' => 1100, 'reasoning_tokens' => 1000, 'total_tokens' => 2103],
        'completed_at' => '2026-11-04T14:03:29Z',
    ]);
}

it('charges the actual price and gives back the rest of the hold', function (): void {
    $user = User::factory()->withCredits('1')->create();
    $generation = heldGeneration($user);

    resolve(ApplyGenerationSnapshot::class)->handle(successFor($generation));

    expect((string) resolve(GetCreditBalance::class)->handle($user))->toBe('0.929356250000')
        ->and($generation->creditTransactions()->pluck('type')->all())
        ->toEqualCanonicalizing([CreditTransactionType::Hold, CreditTransactionType::Release, CreditTransactionType::Charge]);
});

it('gives back the whole hold when pixelforge fails the generation', function (): void {
    $user = User::factory()->withCredits('1')->create();
    $generation = heldGeneration($user);

    resolve(ApplyGenerationSnapshot::class)->handle(pixelforgeSays($generation, [
        'status' => 'failed',
        'error' => ['code' => 'generation_failed', 'message' => 'No image.'],
    ]));

    expect(resolve(GetCreditBalance::class)->handle($user)->compare(1))->toBe(0)
        ->and($generation->creditTransactions()->where('type', CreditTransactionType::Charge)->exists())->toBeFalse();
});

it('gives back the whole hold when submission fails', function (): void {
    $user = User::factory()->withCredits('1')->create();
    $generation = heldGeneration($user);

    resolve(MarkGenerationAsFailed::class)->handle($generation, 'content_policy_violation', 'Blocked.');

    expect(resolve(GetCreditBalance::class)->handle($user)->compare(1))->toBe(0);
});

it('never charges or releases twice when the success arrives again', function (): void {
    $user = User::factory()->withCredits('1')->create();
    $generation = heldGeneration($user);

    resolve(ApplyGenerationSnapshot::class)->handle(successFor($generation));
    resolve(ApplyGenerationSnapshot::class)->handle(successFor($generation));

    expect($generation->creditTransactions()->count())->toBe(3)
        ->and((string) resolve(GetCreditBalance::class)->handle($user))->toBe('0.929356250000');
});

it('keeps the hold while the generation is still in flight', function (): void {
    $user = User::factory()->withCredits('1')->create();
    $generation = heldGeneration($user);

    resolve(ApplyGenerationSnapshot::class)->handle(pixelforgeSays($generation, ['status' => 'processing']));

    expect((string) resolve(GetCreditBalance::class)->handle($user))->toBe('0.912500000000');
});

it('refuses a second ledger entry of the same kind for one generation at the database level', function (): void {
    $user = User::factory()->withCredits('1')->create();
    $generation = heldGeneration($user);

    $generation->creditTransactions()->create([
        'user_id' => $user->id,
        'type' => CreditTransactionType::Hold,
        'amount' => '-0.0875',
    ]);
})->throws(UniqueConstraintViolationException::class);
