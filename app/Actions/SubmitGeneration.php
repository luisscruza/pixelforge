<?php

declare(strict_types=1);

namespace App\Actions;

use App\Data\GenerationInput;
use App\Enums\GenerationStatus;
use App\Http\Integrations\Pixelforge\PixelforgeConnector;
use App\Http\Integrations\Pixelforge\PixelforgeException;
use App\Models\Generation;

final readonly class SubmitGeneration
{
    public function __construct(
        private PixelforgeConnector $pixelforge,
        private ApplyGenerationSnapshot $applySnapshot,
        private MarkGenerationAsFailed $markAsFailed,
    ) {
        //
    }

    public function handle(Generation $generation): void
    {
        $generation->refresh();

        if ($generation->status !== GenerationStatus::Pending) {
            return;
        }

        $generation->increment('submission_attempts', 1, ['submitted_at' => now()]);

        try {
            $snapshot = $this->pixelforge->createGeneration(
                new GenerationInput(
                    prompt: $generation->prompt,
                    size: $generation->size,
                    quality: $generation->quality,
                    referenceImages: $generation->reference_images,
                ),
                idempotencyKey: $generation->uuid,
                metadata: [ApplyGenerationSnapshot::METADATA_KEY => $generation->uuid],
            );
        } catch (PixelforgeException $pixelforgeException) {
            throw_if($pixelforgeException->isRetryable(), $pixelforgeException);

            $this->markAsFailed->handle($generation, $pixelforgeException->errorCode, $pixelforgeException->getMessage());

            return;
        }

        $this->applySnapshot->handle($snapshot);
    }
}
