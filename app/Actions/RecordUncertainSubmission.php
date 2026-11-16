<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\GenerationStatus;
use App\Enums\SubmissionStatus;
use App\Models\Generation;

final readonly class RecordUncertainSubmission
{
    public function handle(Generation $generation, SubmissionStatus $status): void
    {
        Generation::query()
            ->whereKey($generation->id)
            ->where('status', GenerationStatus::Pending)
            ->whereIn('submission_status', $this->replaceable($status))
            ->update(['submission_status' => $status, 'updated_at' => now()]);
    }

    /**
     * @return list<SubmissionStatus>
     */
    private function replaceable(SubmissionStatus $status): array
    {
        return match ($status) {
            SubmissionStatus::NeedsReconciliation => [SubmissionStatus::Submitting, SubmissionStatus::Unknown],
            default => [SubmissionStatus::Submitting],
        };
    }
}
