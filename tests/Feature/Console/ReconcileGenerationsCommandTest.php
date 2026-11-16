<?php

declare(strict_types=1);

use Illuminate\Console\Scheduling\Event;
use Illuminate\Console\Scheduling\Schedule;

it('reports what it reconciled', function (): void {
    $this->artisan('pixelforge:reconcile')
        ->expectsOutputToContain('Re-submitted 0, reconciled 0, polled 0.')
        ->assertSuccessful();
});

it('runs every minute without overlapping', function (): void {
    $event = collect(resolve(Schedule::class)->events())
        ->first(fn (Event $event): bool => str_contains((string) $event->command, 'pixelforge:reconcile'));

    expect($event)->not->toBeNull()
        ->and($event?->expression)->toBe('* * * * *')
        ->and($event?->withoutOverlapping)->toBeTrue();
});
