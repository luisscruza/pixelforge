<?php

declare(strict_types=1);

use App\Enums\GenerationStatus;
use App\Models\Generation;
use BcMath\Number;

it('stores money with twelve decimals and reads it back as an exact number', function (): void {
    $generation = Generation::factory()->create(['estimated_price' => '0.467343750001']);

    $price = $generation->fresh()?->estimated_price;

    expect($price)->toBeInstanceOf(Number::class)
        ->and((string) $price)->toBe('0.467343750001');
});

it('generates its own idempotency uuid', function (): void {
    $generation = Generation::factory()->create();

    expect($generation->uuid)->toBeUuid();
});

it('has no usage until every token count is known', function (): void {
    $pending = Generation::factory()->create();
    $succeeded = Generation::factory()->succeeded()->create();

    expect($pending->usage())->toBeNull()
        ->and($succeeded->usage()?->total())->toBe(2103)
        ->and($succeeded->billedRates()?->output->compare('40'))->toBe(0);
});

it('only moves forward through its lifecycle', function (GenerationStatus $from, GenerationStatus $to, bool $allowed): void {
    expect($from->canTransitionTo($to))->toBe($allowed);
})->with([
    'queued to processing' => [GenerationStatus::Queued, GenerationStatus::Processing, true],
    'queued straight to succeeded' => [GenerationStatus::Queued, GenerationStatus::Succeeded, true],
    'processing back to queued' => [GenerationStatus::Processing, GenerationStatus::Queued, false],
    'succeeded back to processing' => [GenerationStatus::Succeeded, GenerationStatus::Processing, false],
    'succeeded to failed' => [GenerationStatus::Succeeded, GenerationStatus::Failed, false],
    'failed to succeeded' => [GenerationStatus::Failed, GenerationStatus::Succeeded, false],
]);
