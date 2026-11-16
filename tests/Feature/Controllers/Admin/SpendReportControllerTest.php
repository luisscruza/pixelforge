<?php

declare(strict_types=1);

use App\Http\Integrations\Pixelforge\Requests\GetUsageRequest;
use App\Models\Generation;
use App\Models\User;
use Carbon\CarbonImmutable;
use Inertia\Testing\AssertableInertia as Assert;
use Saloon\Http\Faking\MockResponse;
use Saloon\Laravel\Facades\Saloon;

it('is only for admins', function (): void {
    $this->actingAs(User::factory()->create())
        ->get(route('admin.spend-report.index'))
        ->assertForbidden();
});

it('defaults to the last fourteen utc days and defers the report', function (): void {
    $this->travelTo(CarbonImmutable::parse('2026-11-16 10:00:00'));
    Saloon::fake([GetUsageRequest::class => MockResponse::make(['object' => 'usage', 'data' => []])]);
    Generation::factory()->succeeded()->create(['completed_at' => CarbonImmutable::parse('2026-11-15 09:00:00')]);

    $this->actingAs(User::factory()->admin()->create())
        ->get(route('admin.spend-report.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page): Assert => $page
            ->component('admin/SpendReport')
            ->where('filters.start_date', '2026-11-03')
            ->where('filters.end_date', '2026-11-16')
            ->missing('report')
            ->loadDeferredProps(fn (Assert $reload): Assert => $reload
                ->has('report.days', 14)
                ->where('report.totals.charged', '0.070643750000')));
});

it('reports a custom range', function (): void {
    Saloon::fake([GetUsageRequest::class => MockResponse::make(['object' => 'usage', 'data' => []])]);

    $this->actingAs(User::factory()->admin()->create())
        ->get(route('admin.spend-report.index', ['start_date' => '2026-11-01', 'end_date' => '2026-11-03']))
        ->assertInertia(fn (Assert $page): Assert => $page
            ->where('filters.start_date', '2026-11-01')
            ->loadDeferredProps(fn (Assert $reload): Assert => $reload->has('report.days', 3)));
});

it('rejects ranges pixelforge cannot report on', function (array $query, string $field): void {
    $this->actingAs(User::factory()->admin()->create())
        ->get(route('admin.spend-report.index', $query))
        ->assertSessionHasErrors($field);
})->with([
    'end before start' => [['start_date' => '2026-11-10', 'end_date' => '2026-11-01'], 'end_date'],
    'more than 92 days' => [['start_date' => '2026-01-01', 'end_date' => '2026-11-01'], 'end_date'],
    'not a date' => [['start_date' => 'yesterday'], 'start_date'],
]);
