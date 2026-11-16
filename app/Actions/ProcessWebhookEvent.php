<?php

declare(strict_types=1);

namespace App\Actions;

use App\Data\GenerationData;
use App\Models\WebhookEvent;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Fluent;
use Illuminate\Support\Str;

final readonly class ProcessWebhookEvent
{
    public function __construct(
        private ApplyGenerationSnapshot $applySnapshot,
    ) {
        //
    }

    public function handle(string $eventId): void
    {
        DB::transaction(function () use ($eventId): void {
            $event = WebhookEvent::query()->lockForUpdate()->find($eventId);

            if (! $event instanceof WebhookEvent || $event->processed_at !== null) {
                return;
            }

            if (Str::startsWith($event->type, 'generation.')) {
                $this->applySnapshot->handle(
                    GenerationData::fromArray(new Fluent($event->payload)->array('data.object')),
                );
            }

            $event->update(['processed_at' => now()]);
        });
    }
}
