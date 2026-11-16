<?php

declare(strict_types=1);

use App\Actions\CreateGeneration;
use App\Data\CostEstimate;
use App\Data\GenerationInput;
use App\Enums\GenerationQuality;
use App\Enums\GenerationSize;
use App\Enums\GenerationStatus;
use App\Jobs\SubmitGenerationJob;
use App\Models\User;
use BcMath\Number;
use Illuminate\Support\Facades\Queue;

it('stores a pending generation and queues its submission', function (): void {
    Queue::fake();
    $user = User::factory()->create();

    $generation = resolve(CreateGeneration::class)->handle(
        $user,
        new GenerationInput('A lighthouse', GenerationSize::Portrait, GenerationQuality::High, ['https://example.com/style.png']),
        new CostEstimate(new Number('0.3'), new Number('0.37'), new Number('0.375'), new Number('0.4625')),
    );

    expect($generation->user_id)->toBe($user->id)
        ->and($generation->status)->toBe(GenerationStatus::Pending)
        ->and($generation->reference_images)->toBe(['https://example.com/style.png'])
        ->and((string) $generation->refresh()->estimated_price)->toBe('0.375000000000')
        ->and((string) $generation->maximum_price)->toBe('0.462500000000');

    Queue::assertPushed(SubmitGenerationJob::class, fn (SubmitGenerationJob $job): bool => $job->generation->is($generation));
});
