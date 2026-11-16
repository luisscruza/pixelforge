<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\GenerationStatus;
use App\Enums\SubmissionStatus;
use App\Models\Generation;

final readonly class AbandonSubmission
{
    public function __construct(
        private RecordUncertainSubmission $recordUncertainSubmission,
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

        if ($generation->submission_status === SubmissionStatus::Unknown) {
            $this->recordUncertainSubmission->handle($generation, SubmissionStatus::NeedsReconciliation);

            return;
        }

        $this->markAsFailed->handle(
            $generation,
            'submission_failed',
            'The studio was too busy to take this design. You were not charged.',
        );
    }
}
