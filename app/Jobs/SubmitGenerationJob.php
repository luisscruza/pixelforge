<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Actions\MarkGenerationAsFailed;
use App\Actions\SubmitGeneration;
use App\Http\Integrations\Pixelforge\PixelforgeException;
use App\Models\Generation;
use DateTimeInterface;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\Attributes\DeleteWhenMissingModels;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Saloon\Exceptions\Request\FatalRequestException;
use Throwable;

#[DeleteWhenMissingModels]
final class SubmitGenerationJob implements ShouldQueue
{
    use Queueable;

    public const int RETRY_WINDOW_MINUTES = 10;

    private const int MAX_BACKOFF_SECONDS = 60;

    public function __construct(
        public Generation $generation,
    ) {
        //
    }

    public function retryUntil(): DateTimeInterface
    {
        return now()->addMinutes(self::RETRY_WINDOW_MINUTES);
    }

    /**
     * @return list<object>
     */
    public function middleware(): array
    {
        return [new WithoutOverlapping((string) $this->generation->id)->releaseAfter(5)->expireAfter(60)];
    }

    public function handle(SubmitGeneration $submitGeneration): void
    {
        try {
            $submitGeneration->handle($this->generation);
        } catch (PixelforgeException $exception) {
            $this->release($this->delayFor($exception->retryAfterSeconds));
        } catch (FatalRequestException) {
            $this->release($this->delayFor(null));
        }
    }

    public function failed(?Throwable $exception): void
    {
        resolve(MarkGenerationAsFailed::class)->handle(
            $this->generation,
            'submission_failed',
            'Pixelforge could not accept this generation. You were not charged.',
        );
    }

    private function delayFor(?int $retryAfterSeconds): int
    {
        $backoff = min(2 ** $this->attempts(), self::MAX_BACKOFF_SECONDS);

        return max($retryAfterSeconds ?? 0, $backoff);
    }
}
