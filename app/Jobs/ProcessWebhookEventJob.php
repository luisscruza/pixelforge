<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Actions\ProcessWebhookEvent;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\Attributes\Backoff;
use Illuminate\Queue\Attributes\Tries;

#[Backoff([5, 30, 120])]
#[Tries(5)]
final class ProcessWebhookEventJob implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public string $eventId,
    ) {
        //
    }

    public function handle(ProcessWebhookEvent $processWebhookEvent): void
    {
        $processWebhookEvent->handle($this->eventId);
    }
}
