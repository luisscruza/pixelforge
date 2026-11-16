<?php

declare(strict_types=1);

namespace App\Actions;

use App\Data\ReconciliationResult;
use App\Enums\GenerationStatus;
use App\Enums\SubmissionStatus;
use App\Http\Integrations\Pixelforge\PixelforgeConnector;
use App\Http\Integrations\Pixelforge\PixelforgeException;
use App\Jobs\SubmitGenerationJob;
use App\Models\Generation;
use Illuminate\Foundation\Bus\PendingDispatch;
use Saloon\Exceptions\Request\FatalRequestException;

final readonly class ReconcileGenerations
{
    private const int STALE_AFTER_MINUTES = 2;

    private const int RECONCILE_EVERY_MINUTES = 15;

    private const int MAX_REQUESTS_PER_RUN = 20;

    public function __construct(
        private PixelforgeConnector $pixelforge,
        private ApplyGenerationSnapshot $applySnapshot,
        private SubmitGeneration $submitGeneration,
    ) {
        //
    }

    public function handle(): ReconciliationResult
    {
        return new ReconciliationResult(
            resubmitted: $this->resubmitStalledSubmissions(),
            reconciled: $this->retryUnresolvedSubmissions(),
            polled: $this->pollStuckInFlight(),
        );
    }

    private function resubmitStalledSubmissions(): int
    {
        $stalled = Generation::query()
            ->where('status', GenerationStatus::Pending)
            ->whereIn('submission_status', [SubmissionStatus::Submitting, SubmissionStatus::Unknown])
            ->where('updated_at', '<', now()->subMinutes(self::STALE_AFTER_MINUTES))
            ->get();

        $stalled->each(fn (Generation $generation): PendingDispatch => dispatch(new SubmitGenerationJob($generation)));

        return $stalled->count();
    }

    private function retryUnresolvedSubmissions(): int
    {
        $unresolved = Generation::query()
            ->where('status', GenerationStatus::Pending)
            ->where('submission_status', SubmissionStatus::NeedsReconciliation)
            ->where('submitted_at', '<', now()->subMinutes(self::RECONCILE_EVERY_MINUTES))
            ->oldest('submitted_at')
            ->limit(self::MAX_REQUESTS_PER_RUN)
            ->get();

        $attempted = 0;

        foreach ($unresolved as $generation) {
            $attempted++;

            try {
                $this->submitGeneration->handle($generation);
            } catch (PixelforgeException|FatalRequestException) {
                break;
            }
        }

        return $attempted;
    }

    private function pollStuckInFlight(): int
    {
        $stuck = Generation::query()
            ->whereIn('status', [GenerationStatus::Queued, GenerationStatus::Processing])
            ->whereNotNull('pixelforge_id')
            ->where('updated_at', '<', now()->subMinutes(self::STALE_AFTER_MINUTES))
            ->oldest('updated_at')
            ->limit(self::MAX_REQUESTS_PER_RUN)
            ->get();

        $polled = 0;

        foreach ($stuck as $generation) {
            try {
                $this->applySnapshot->handle($this->pixelforge->generation((string) $generation->pixelforge_id));
                $polled++;
            } catch (PixelforgeException $exception) {
                if ($exception->isRetryable()) {
                    break;
                }
            } catch (FatalRequestException) {
                break;
            }
        }

        return $polled;
    }
}
