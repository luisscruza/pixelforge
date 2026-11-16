<?php

declare(strict_types=1);

use App\Enums\GenerationStatus;
use App\Models\Generation;
use App\Models\WebhookEvent;
use Illuminate\Testing\TestResponse;

function succeededPayload(Generation $generation): array
{
    return [
        'id' => $generation->pixelforge_id,
        'status' => 'succeeded',
        'metadata' => ['generation_uuid' => $generation->uuid],
        'usage' => ['input_text_tokens' => 3, 'input_image_tokens' => 0, 'output_tokens' => 1100, 'reasoning_tokens' => 1000, 'total_tokens' => 2103],
        'image_url' => 'https://pixelforge.promptedit.dev/images/gen_1.png',
        'image_url_expires_at' => '2026-11-05T14:03:29Z',
        'started_at' => '2026-11-04T14:03:13Z',
        'completed_at' => '2026-11-04T14:03:29Z',
    ];
}

it('records a signed event and applies it to the generation', function (): void {
    $generation = Generation::factory()->processing()->create();
    $event = pixelforgeEvent('generation.succeeded', succeededPayload($generation));

    sendPixelforgeWebhook($event)->assertNoContent();

    $generation->refresh();

    expect($generation->status)->toBe(GenerationStatus::Succeeded)
        ->and((string) $generation->price)->toBe('0.070643750000')
        ->and(WebhookEvent::query()->find($event['id'])?->processed_at)->not->toBeNull();
});

it('records an event delivered twice only once', function (): void {
    $generation = Generation::factory()->processing()->create();
    $event = pixelforgeEvent('generation.succeeded', succeededPayload($generation));

    sendPixelforgeWebhook($event)->assertNoContent();
    sendPixelforgeWebhook($event)->assertNoContent();

    expect(WebhookEvent::query()->count())->toBe(1)
        ->and((string) $generation->refresh()->price)->toBe('0.070643750000');
});

it('reaches the right state when events arrive out of order', function (): void {
    $generation = Generation::factory()->queued()->create();

    sendPixelforgeWebhook(pixelforgeEvent('generation.succeeded', succeededPayload($generation)))->assertNoContent();
    sendPixelforgeWebhook(pixelforgeEvent('generation.processing', [
        'id' => $generation->pixelforge_id,
        'status' => 'processing',
        'metadata' => ['generation_uuid' => $generation->uuid],
    ]))->assertNoContent();

    expect($generation->refresh()->status)->toBe(GenerationStatus::Succeeded)
        ->and(WebhookEvent::query()->count())->toBe(2);
});

it('records a failure at no cost', function (): void {
    $generation = Generation::factory()->processing()->create();

    sendPixelforgeWebhook(pixelforgeEvent('generation.failed', [
        'id' => $generation->pixelforge_id,
        'status' => 'failed',
        'metadata' => ['generation_uuid' => $generation->uuid],
        'error' => ['code' => 'internal_error', 'message' => 'Please retry.'],
        'completed_at' => '2026-11-04T14:03:29Z',
    ]))->assertNoContent();

    expect($generation->refresh()->status)->toBe(GenerationStatus::Failed)
        ->and($generation->price?->compare(0))->toBe(0);
});

it('acknowledges test pings from the dashboard', function (): void {
    $event = pixelforgeEvent('ping');

    sendPixelforgeWebhook($event)->assertNoContent();

    expect(WebhookEvent::query()->find($event['id'])?->processed_at)->not->toBeNull();
});

it('acknowledges events for generations from another environment', function (): void {
    sendPixelforgeWebhook(pixelforgeEvent('generation.processing', ['id' => 'gen_ELSEWHERE', 'status' => 'processing']))
        ->assertNoContent();

    expect(Generation::query()->count())->toBe(0);
});

it('rejects requests that are not signed by pixelforge', function (Closure $send): void {
    $generation = Generation::factory()->processing()->create();
    $event = pixelforgeEvent('generation.succeeded', succeededPayload($generation));

    $send($event)->assertBadRequest();

    expect(WebhookEvent::query()->count())->toBe(0)
        ->and($generation->refresh()->status)->toBe(GenerationStatus::Processing);
})->with([
    'wrong secret' => fn (array $event): TestResponse => sendPixelforgeWebhook($event, secret: 'whsec_attacker'),
    'replayed after five minutes' => fn (array $event): TestResponse => sendPixelforgeWebhook($event, timestamp: now()->subSeconds(301)->getTimestamp()),
    'timestamp from the future' => fn (array $event): TestResponse => sendPixelforgeWebhook($event, timestamp: now()->addSeconds(301)->getTimestamp()),
    'body changed after signing' => function (array $event) {
        $timestamp = now()->getTimestamp();
        $signature = hash_hmac('sha256', $timestamp.'.'.json_encode($event), 'whsec_test');
        $event['data']['object']['usage']['output_tokens'] = 1;

        return test()->call('POST', '/webhooks/pixelforge', server: [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_PIXELFORGE_SIGNATURE' => "t={$timestamp},v1={$signature}",
        ], content: json_encode($event));
    },
    'no signature header' => fn (array $event) => test()->call('POST', '/webhooks/pixelforge', server: ['CONTENT_TYPE' => 'application/json'], content: json_encode($event)),
]);

it('accepts a timestamp just inside the tolerance', function (): void {
    sendPixelforgeWebhook(pixelforgeEvent('ping'), timestamp: now()->subSeconds(300)->getTimestamp())->assertNoContent();
});

it('rejects everything when no signing secret is configured', function (): void {
    config(['services.pixelforge.webhook_secret' => null]);

    sendPixelforgeWebhook(pixelforgeEvent('ping'))->assertBadRequest();
});

it('rejects signed events that are malformed', function (array $event): void {
    sendPixelforgeWebhook($event)->assertUnprocessable();

    expect(WebhookEvent::query()->count())->toBe(0);
})->with([
    'missing id' => [['type' => 'ping', 'data' => []]],
    'id is not an event' => [['id' => 'gen_123', 'type' => 'ping', 'data' => []]],
    'missing type' => [['id' => 'evt_123', 'data' => []]],
]);
