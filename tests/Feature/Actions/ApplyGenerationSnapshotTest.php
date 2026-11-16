<?php

declare(strict_types=1);

use App\Actions\ApplyGenerationSnapshot;
use App\Data\GenerationData;
use App\Enums\GenerationStatus;
use App\Models\Generation;

function snapshotFor(Generation $generation, array $overrides = []): GenerationData
{
    return GenerationData::fromArray(pixelforgeGeneration([
        'id' => $generation->pixelforge_id ?? 'gen_01JC2Z8Q4N6V0M3H7X5T9R2B1K',
        'metadata' => ['generation_uuid' => $generation->uuid],
        ...$overrides,
    ]));
}

function succeededSnapshot(Generation $generation, array $overrides = []): GenerationData
{
    return snapshotFor($generation, [
        'status' => 'succeeded',
        'usage' => ['input_text_tokens' => 12, 'input_image_tokens' => 765, 'output_tokens' => 6450, 'reasoning_tokens' => 3184, 'total_tokens' => 10411],
        'image_url' => 'https://pixelforge.promptedit.dev/images/gen_1.png',
        'image_url_expires_at' => '2026-11-05T14:03:29Z',
        'started_at' => '2026-11-04T14:03:13Z',
        'completed_at' => '2026-11-04T14:03:29Z',
        ...$overrides,
    ]);
}

it('records the actual cost of a succeeded generation', function (): void {
    $generation = Generation::factory()->processing()->create();

    resolve(ApplyGenerationSnapshot::class)->handle(succeededSnapshot($generation));

    $generation->refresh();

    expect($generation->status)->toBe(GenerationStatus::Succeeded)
        ->and((string) $generation->provider_cost)->toBe('0.305510000000')
        ->and((string) $generation->price)->toBe('0.381887500000')
        ->and($generation->usage()?->total())->toBe(10411)
        ->and($generation->image_url)->toBe('https://pixelforge.promptedit.dev/images/gen_1.png')
        ->and($generation->completed_at?->toIso8601ZuluString())->toBe('2026-11-04T14:03:29Z');
});

it('bills at the rates fixed on the generation, not current pricing', function (): void {
    $generation = Generation::factory()->processing()->create();

    resolve(ApplyGenerationSnapshot::class)->handle(succeededSnapshot($generation, [
        'rates' => [...pixelforgeRates(), 'output_tokens' => '20.00'],
    ]));

    expect((string) $generation->refresh()->provider_cost)->toBe('0.176510000000');
});

it('charges nothing for a failed generation', function (): void {
    $generation = Generation::factory()->processing()->create();

    resolve(ApplyGenerationSnapshot::class)->handle(snapshotFor($generation, [
        'status' => 'failed',
        'error' => ['code' => 'generation_failed', 'message' => 'The model could not produce an image.'],
        'completed_at' => '2026-11-04T14:03:29Z',
    ]));

    $generation->refresh();

    expect($generation->status)->toBe(GenerationStatus::Failed)
        ->and($generation->price?->compare(0))->toBe(0)
        ->and($generation->provider_cost?->compare(0))->toBe(0)
        ->and($generation->error_code)->toBe('generation_failed');
});

it('applies the same success twice without charging twice', function (): void {
    $generation = Generation::factory()->processing()->create();
    $action = resolve(ApplyGenerationSnapshot::class);

    $action->handle(succeededSnapshot($generation));
    $action->handle(succeededSnapshot($generation, [
        'usage' => ['input_text_tokens' => 99, 'input_image_tokens' => 0, 'output_tokens' => 1, 'reasoning_tokens' => 1, 'total_tokens' => 101],
    ]));

    expect((string) $generation->refresh()->price)->toBe('0.381887500000');
});

it('ignores a late processing event after the generation finished', function (string $finalStatus): void {
    $generation = Generation::factory()->{$finalStatus}()->create();
    $priceBefore = $generation->price;

    resolve(ApplyGenerationSnapshot::class)->handle(snapshotFor($generation, ['status' => 'processing']));

    $generation->refresh();

    expect($generation->status->value)->toBe($finalStatus)
        ->and($generation->price?->compare($priceBefore ?? 0))->toBe(0);
})->with(['succeeded', 'failed']);

it('never turns a failure into a success', function (): void {
    $generation = Generation::factory()->failed()->create();

    resolve(ApplyGenerationSnapshot::class)->handle(succeededSnapshot($generation));

    expect($generation->refresh()->status)->toBe(GenerationStatus::Failed)
        ->and($generation->price?->compare(0))->toBe(0);
});

it('handles a webhook that arrives before our submit request returned', function (): void {
    $generation = Generation::factory()->create(['status' => GenerationStatus::Pending]);

    resolve(ApplyGenerationSnapshot::class)->handle(snapshotFor($generation, [
        'id' => 'gen_EARLY',
        'status' => 'processing',
        'started_at' => '2026-11-04T14:03:13Z',
    ]));

    $generation->refresh();

    expect($generation->pixelforge_id)->toBe('gen_EARLY')
        ->and($generation->status)->toBe(GenerationStatus::Processing)
        ->and($generation->rates)->toEqual(pixelforgeRates());
});

it('finds the generation by pixelforge id when metadata is missing', function (): void {
    $generation = Generation::factory()->queued()->create();

    resolve(ApplyGenerationSnapshot::class)->handle(GenerationData::fromArray(pixelforgeGeneration([
        'id' => $generation->pixelforge_id,
        'status' => 'processing',
    ])));

    expect($generation->refresh()->status)->toBe(GenerationStatus::Processing);
});

it('ignores generations that are not ours', function (): void {
    $result = resolve(ApplyGenerationSnapshot::class)->handle(GenerationData::fromArray(pixelforgeGeneration([
        'id' => 'gen_SOMEONE_ELSE',
        'status' => 'processing',
    ])));

    expect($result)->toBeNull();
});

it('rejects a success that carries no usage', function (): void {
    GenerationData::fromArray(pixelforgeGeneration(['status' => 'succeeded', 'usage' => null]));
})->throws(UnexpectedValueException::class);
