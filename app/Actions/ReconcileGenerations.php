<?php

declare(strict_types=1);

namespace App\Actions;

use App\Data\ReconciliationResult;
use App\Enums\GenerationStatus;
use App\Http\Integrations\Pixelforge\PixelforgeConnector;
use App\Http\Integrations\Pixelforge\PixelforgeException;
use App\Jobs\SubmitGenerationJob;
use App\Models\Generation;
use Illuminate\Foundation\Bus\PendingDispatch;
use Saloon\Exceptions\Request\FatalRequestException;

final readonly class ReconcileGenerations
{
    private const int STALE_AFTER_MINUTES = 2;

    private const int ABANDON_PENDING_AFTER_MINUTES = 30;

    private const int MAX_POLLS_PER_RUN = 20;

    public function __construct(
        private PixelforgeConnector $pixelforge,
        private ApplyGenerationSnapshot $applySnapshot,
        private MarkGenerationAsFailed $markAsFailed,
    ) {
        //
    }

    public function handle(): ReconciliationResult
    {
        return new ReconciliationResult(
            resubmitted: $this->resubmitStalledPending(),
            abandoned: $this->abandonExpiredPending(),
            polled: $this->pollStuckInFlight(),
        );
    }

    private function resubmitStalledPending(): int
    {
        $stalled = Generation::query()
            ->where('status', GenerationStatus::Pending)
            ->where('updated_at', '<', now()->subMinutes(self::STALE_AFTER_MINUTES))
            ->where('created_at', '>=', now()->subMinutes(self::ABANDON_PENDING_AFTER_MINUTES))
            ->get();

        $stalled->each(fn (Generation $generation): PendingDispatch => dispatch(new SubmitGenerationJob($generation)));

        return $stalled->count();
    }

    private function abandonExpiredPending(): int
    {
        $expired = Generation::query()
            ->where('status', GenerationStatus::Pending)
            ->where('created_at', '<', now()->subMinutes(self::ABANDON_PENDING_AFTER_MINUTES))
            ->get();

        $expired->each(fn (Generation $generation) => $this->markAsFailed->handle(
            $generation,
            'submission_failed',
            'Pixelforge could not accept this generation. You were not charged.',
        ));

        return $expired->count();
    }

    private function pollStuckInFlight(): int
    {
        $stuck = Generation::query()
            ->whereIn('status', [GenerationStatus::Queued, GenerationStatus::Processing])
            ->whereNotNull('pixelforge_id')
            ->where('updated_at', '<', now()->subMinutes(self::STALE_AFTER_MINUTES))
            ->oldest('updated_at')
            ->limit(self::MAX_POLLS_PER_RUN)
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
