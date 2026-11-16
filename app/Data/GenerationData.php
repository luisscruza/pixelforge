<?php

declare(strict_types=1);

namespace App\Data;

use App\Enums\GenerationStatus;
use Carbon\CarbonImmutable;
use Illuminate\Support\Fluent;
use UnexpectedValueException;

final readonly class GenerationData
{
    /**
     * @param  array<array-key, mixed>  $metadata
     */
    public function __construct(
        public string $id,
        public GenerationStatus $status,
        public Rates $rates,
        public array $metadata,
        public ?TokenUsage $usage = null,
        public ?string $imageUrl = null,
        public ?CarbonImmutable $imageUrlExpiresAt = null,
        public ?string $errorCode = null,
        public ?string $errorMessage = null,
        public ?CarbonImmutable $startedAt = null,
        public ?CarbonImmutable $completedAt = null,
    ) {
        //
    }

    /**
     * @param  array<array-key, mixed>  $generation
     */
    public static function fromArray(array $generation): self
    {
        $data = new Fluent($generation);

        $status = $data->enum('status', GenerationStatus::class);

        throw_if(! $status instanceof GenerationStatus || $status === GenerationStatus::Pending, UnexpectedValueException::class, 'Pixelforge returned an unknown generation status.');

        return new self(
            id: $data->string('id')->value(),
            status: $status,
            rates: Rates::fromArray($data->array('rates')),
            metadata: $data->array('metadata'),
            usage: $data->filled('usage') ? TokenUsage::fromArray($data->array('usage')) : null,
            imageUrl: $data->filled('image_url') ? $data->string('image_url')->value() : null,
            imageUrlExpiresAt: self::timestamp($data, 'image_url_expires_at'),
            errorCode: $data->filled('error.code') ? $data->string('error.code')->value() : null,
            errorMessage: $data->filled('error.message') ? $data->string('error.message')->value() : null,
            startedAt: self::timestamp($data, 'started_at'),
            completedAt: self::timestamp($data, 'completed_at'),
        );
    }

    public function metadataValue(string $key): ?string
    {
        $value = $this->metadata[$key] ?? null;

        return is_string($value) ? $value : null;
    }

    /**
     * @param  Fluent<array-key, mixed>  $data
     */
    private static function timestamp(Fluent $data, string $key): ?CarbonImmutable
    {
        return $data->filled($key) ? CarbonImmutable::parse($data->string($key)->value()) : null;
    }
}
