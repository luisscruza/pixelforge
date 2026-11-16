<?php

declare(strict_types=1);

namespace App\Actions;

use App\Jobs\ProcessWebhookEventJob;
use App\Models\WebhookEvent;
use Illuminate\Support\Fluent;

final readonly class RecordWebhookEvent
{
    /**
     * @param  array<array-key, mixed>  $event
     */
    public function handle(array $event): bool
    {
        $data = new Fluent($event);
        $id = $data->string('id')->value();

        $inserted = WebhookEvent::query()->insertOrIgnore([
            'id' => $id,
            'type' => $data->string('type')->value(),
            'pixelforge_generation_id' => $data->filled('data.object.id') ? $data->string('data.object.id')->value() : null,
            'payload' => json_encode($event, JSON_THROW_ON_ERROR),
            'created_at' => now(),
            'updated_at' => now(),
        ]) === 1;

        if ($inserted) {
            dispatch(new ProcessWebhookEventJob($id));
        }

        return $inserted;
    }
}
