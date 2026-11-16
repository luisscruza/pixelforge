<?php

declare(strict_types=1);

namespace App\Actions;

use App\Data\GenerationData;
use App\Data\TokenUsage;
use App\Enums\GenerationStatus;
use App\Models\Generation;
use BcMath\Number;
use Illuminate\Support\Facades\DB;

final readonly class ApplyGenerationSnapshot
{
    public const string METADATA_KEY = 'generation_uuid';

    public function __construct(
        private CalculateProviderCost $calculateProviderCost,
        private ApplyMarkup $applyMarkup,
    ) {
        //
    }

    public function handle(GenerationData $snapshot): ?Generation
    {
        return DB::transaction(function () use ($snapshot): ?Generation {
            $generation = $this->lockGeneration($snapshot);

            if (! $generation instanceof Generation) {
                return null;
            }

            $generation->pixelforge_id ??= $snapshot->id;
            $generation->rates ??= $snapshot->rates->toArray();

            if ($generation->status->canTransitionTo($snapshot->status)) {
                $this->transition($generation, $snapshot);
            }

            $generation->save();

            return $generation;
        });
    }

    private function lockGeneration(GenerationData $snapshot): ?Generation
    {
        $uuid = $snapshot->metadataValue(self::METADATA_KEY);

        return Generation::query()
            ->when(
                $uuid !== null,
                fn ($query) => $query->where('uuid', $uuid),
                fn ($query) => $query->where('pixelforge_id', $snapshot->id),
            )
            ->lockForUpdate()
            ->first();
    }

    private function transition(Generation $generation, GenerationData $snapshot): void
    {
        $generation->status = $snapshot->status;
        $generation->started_at ??= $snapshot->startedAt;

        if ($snapshot->status === GenerationStatus::Succeeded && $snapshot->usage instanceof TokenUsage) {
            $providerCost = $this->calculateProviderCost->handle($snapshot->usage, $snapshot->rates);

            $generation->input_text_tokens = $snapshot->usage->inputText;
            $generation->input_image_tokens = $snapshot->usage->inputImage;
            $generation->output_tokens = $snapshot->usage->output;
            $generation->reasoning_tokens = $snapshot->usage->reasoning;
            $generation->provider_cost = $providerCost;
            $generation->price = $this->applyMarkup->handle($providerCost);
            $generation->image_url = $snapshot->imageUrl;
            $generation->image_url_expires_at = $snapshot->imageUrlExpiresAt;
            $generation->completed_at = $snapshot->completedAt ?? now();
        }

        if ($snapshot->status === GenerationStatus::Failed) {
            $generation->provider_cost = new Number(0);
            $generation->price = new Number(0);
            $generation->error_code = $snapshot->errorCode;
            $generation->error_message = $snapshot->errorMessage;
            $generation->completed_at = $snapshot->completedAt ?? now();
        }
    }
}
