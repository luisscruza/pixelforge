<?php

declare(strict_types=1);

namespace App\Actions;

use App\Data\GenerationInput;
use App\Enums\GenerationStatus;
use App\Enums\SubmissionStatus;
use App\Http\Integrations\Pixelforge\PixelforgeConnector;
use App\Http\Integrations\Pixelforge\PixelforgeException;
use App\Models\Generation;
use Saloon\Exceptions\Request\FatalRequestException;

final readonly class SubmitGeneration
{
    public function __construct(
        private PixelforgeConnector $pixelforge,
        private ApplyGenerationSnapshot $applySnapshot,
        private MarkGenerationAsFailed $markAsFailed,
        private RecordUncertainSubmission $recordUncertainSubmission,
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
        } catch (FatalRequestException $exception) {
            $this->recordUncertainSubmission->handle($generation, SubmissionStatus::Unknown);

            throw $exception;
        } catch (PixelforgeException $exception) {
            if ($exception->leavesOutcomeUnknown()) {
                $this->recordUncertainSubmission->handle($generation, SubmissionStatus::Unknown);
            }

            throw_if($exception->isRetryable(), $exception);

            $this->markAsFailed->handle($generation, $exception->errorCode, $exception->getMessage());

            return;
        }

        $this->applySnapshot->handle($snapshot);
    }
}
