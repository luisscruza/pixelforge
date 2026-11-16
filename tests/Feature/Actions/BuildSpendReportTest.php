<?php

declare(strict_types=1);

use App\Actions\BuildSpendReport;
use App\Http\Integrations\Pixelforge\Requests\GetUsageRequest;
use App\Models\Generation;
use App\Models\User;
use Carbon\CarbonImmutable;
use Saloon\Http\Faking\MockResponse;
use Saloon\Laravel\Facades\Saloon;

function usageDay(string $date, string $cost, int $generations = 1): array
{
    return ['date' => $date, 'generations' => $generations, 'input_text_tokens' => 0, 'input_image_tokens' => 0, 'output_tokens' => 0, 'reasoning_tokens' => 0, 'cost_usd' => $cost];
}

function fakeUsage(array $days): void
{
    Saloon::fake([GetUsageRequest::class => MockResponse::make(['object' => 'usage', 'data' => $days])]);
}

function completedGeneration(User $user, string $completedAt, string $providerCost, string $price): Generation
{
    return Generation::factory()->succeeded()->for($user)->create([
        'completed_at' => CarbonImmutable::parse($completedAt),
        'provider_cost' => $providerCost,
        'price' => $price,
    ]);
}

function reportFor(string $start, string $end): array
{
    return resolve(BuildSpendReport::class)->handle(CarbonImmutable::parse($start), CarbonImmutable::parse($end))->toArray();
}

it('totals charges, provider costs and profit per day and per user', function (): void {
    $alice = User::factory()->create(['name' => 'Alice']);
    $bob = User::factory()->create(['name' => 'Bob']);
    completedGeneration($alice, '2026-11-01 10:00:00', '0.056515000', '0.07064375');
    completedGeneration($alice, '2026-11-01 11:00:00', '0.305510000', '0.3818875');
    completedGeneration($bob, '2026-11-01 12:00:00', '0.100000000', '0.125');
    fakeUsage([usageDay('2026-11-01', '0.462025000', 3)]);

    $report = reportFor('2026-11-01', '2026-11-01');
    $day = $report['days'][0];

    expect($day['generations'])->toBe(3)
        ->and($day['charged'])->toBe('0.577531250000')
        ->and($day['provider_cost'])->toBe('0.462025000000')
        ->and($day['profit'])->toBe('0.115506250000')
        ->and($day['users'])->toHaveCount(2)
        ->and($day['users'][0]['name'])->toBe('Alice')
        ->and($day['users'][0]['charged'])->toBe('0.452531250000')
        ->and($day['users'][1]['profit'])->toBe('0.025000000000');
});

it('counts a generation on the utc day it completed, like pixelforge bills it', function (): void {
    $user = User::factory()->create();
    Generation::factory()->succeeded()->for($user)->create([
        'created_at' => CarbonImmutable::parse('2026-11-01 23:59:50'),
        'completed_at' => CarbonImmutable::parse('2026-11-02 00:00:10'),
        'provider_cost' => '0.1',
        'price' => '0.125',
    ]);
    fakeUsage([usageDay('2026-11-01', '0.000000000', 0), usageDay('2026-11-02', '0.100000000')]);

    $report = reportFor('2026-11-01', '2026-11-02');

    expect($report['days'][0]['generations'])->toBe(0)
        ->and($report['days'][1]['generations'])->toBe(1)
        ->and($report['days'][1]['difference'])->toBe('0.000000000000');
});

it('includes every day in the range, even without usage', function (): void {
    fakeUsage([]);

    $report = reportFor('2026-11-01', '2026-11-03');

    expect(array_column($report['days'], 'date'))->toBe(['2026-11-01', '2026-11-02', '2026-11-03'])
        ->and($report['days'][1]['charged'])->toBe('0');
});

it('leaves out generations that failed or are still in flight', function (): void {
    $user = User::factory()->create();
    Generation::factory()->failed()->for($user)->create(['completed_at' => CarbonImmutable::parse('2026-11-01 10:00:00')]);
    Generation::factory()->processing()->for($user)->create();
    fakeUsage([usageDay('2026-11-01', '0.000000000', 0)]);

    expect(reportFor('2026-11-01', '2026-11-01')['days'][0]['generations'])->toBe(0);
});

it('flags days where our provider costs disagree with pixelforge', function (): void {
    completedGeneration(User::factory()->create(), '2026-11-01 10:00:00', '0.100000000', '0.125');
    fakeUsage([usageDay('2026-11-01', '0.150000000', 2)]);

    $report = reportFor('2026-11-01', '2026-11-01');

    expect($report['days'][0]['pixelforge_cost'])->toBe('0.150000000')
        ->and($report['days'][0]['difference'])->toBe('-0.050000000000')
        ->and($report['totals']['difference'])->toBe('-0.050000000000');
});

it('still reports our own numbers when pixelforge usage is unavailable', function (): void {
    completedGeneration(User::factory()->create(), '2026-11-01 10:00:00', '0.1', '0.125');
    Saloon::fake([GetUsageRequest::class => MockResponse::make(pixelforgeError('overloaded', 'api_error'), 503)]);

    $report = reportFor('2026-11-01', '2026-11-01');

    expect($report['usage_available'])->toBeFalse()
        ->and($report['days'][0]['charged'])->toBe('0.125000000000')
        ->and($report['days'][0]['pixelforge_cost'])->toBeNull()
        ->and($report['totals']['pixelforge_cost'])->toBeNull();
});

it('ranks users by what they spent across the range', function (): void {
    $small = User::factory()->create(['name' => 'Small spender']);
    $big = User::factory()->create(['name' => 'Big spender']);
    completedGeneration($small, '2026-11-01 10:00:00', '7.2', '9');
    completedGeneration($big, '2026-11-01 10:00:00', '4', '5');
    completedGeneration($big, '2026-11-02 10:00:00', '4', '5');
    fakeUsage([]);

    $totals = reportFor('2026-11-01', '2026-11-02')['user_totals'];

    expect(array_column($totals, 'name'))->toBe(['Big spender', 'Small spender'])
        ->and($totals[0]['charged'])->toBe('10.000000000000')
        ->and($totals[0]['generations'])->toBe(2);
});

it('lets only admins view the spend report', function (): void {
    expect(User::factory()->admin()->create()->can('viewSpendReport'))->toBeTrue()
        ->and(User::factory()->create()->can('viewSpendReport'))->toBeFalse();
});
