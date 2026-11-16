<?php

declare(strict_types=1);

use App\Enums\GenerationStatus;
use App\Http\Integrations\Pixelforge\Requests\GetPricingRequest;
use App\Http\Middleware\HandleInertiaRequests;
use App\Jobs\SubmitGenerationJob;
use App\Models\Generation;
use App\Models\User;
use Illuminate\Support\Facades\Queue;
use Illuminate\Testing\TestResponse;
use Inertia\Testing\AssertableInertia as Assert;
use Saloon\Http\Faking\MockResponse;
use Saloon\Laravel\Facades\Saloon;

beforeEach(function (): void {
    Saloon::fake([GetPricingRequest::class => MockResponse::make(pixelforgePricing())]);
});

function partialStudioReload(User $user, array $only, array $query = []): TestResponse
{
    return test()->actingAs($user)
        ->withHeaders([
            'X-Inertia' => 'true',
            'X-Inertia-Version' => (string) resolve(HandleInertiaRequests::class)->version(request()),
            'X-Inertia-Partial-Component' => 'generations/Index',
            'X-Inertia-Partial-Data' => implode(',', $only),
        ])
        ->get(route('generations.index', $query));
}

it('sends guests to log in', function (): void {
    $this->get(route('generations.index'))->assertRedirect(route('login'));
    $this->post(route('generations.store'))->assertRedirect(route('login'));
});

it('renders the studio with pricing, newest designs first and deferred stats', function (): void {
    $user = User::factory()->create();
    $older = Generation::factory()->succeeded()->for($user)->create(['created_at' => now()->subHour()]);
    $newer = Generation::factory()->queued()->for($user)->create();
    Generation::factory()->succeeded()->create();

    $this->actingAs($user)
        ->get(route('generations.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page): Assert => $page
            ->component('generations/Index')
            ->where('pricing.rates.output_tokens', '40.00')
            ->where('pricing.tokens.per_reference_image', 765)
            ->where('markup', '1.25')
            ->has('generations.data', 2)
            ->where('generations.data.0.id', $newer->uuid)
            ->where('generations.data.1.id', $older->uuid)
            ->missing('stats')
            ->missing('watched')
            ->loadDeferredProps(fn (Assert $reload): Assert => $reload
                ->where('stats.total', 2)
                ->where('stats.succeeded', 1)
                ->where('stats.in_flight', 1)
                ->where('stats.spent', '0.070643750000')));
});

it('shows pending generations as queued and hides expired images', function (): void {
    $user = User::factory()->create();
    Generation::factory()->for($user)->create(['status' => GenerationStatus::Pending]);
    Generation::factory()->succeeded()->for($user)->create([
        'created_at' => now()->subDays(2),
        'image_url_expires_at' => now()->subMinute(),
    ]);

    $this->actingAs($user)
        ->get(route('generations.index'))
        ->assertInertia(fn (Assert $page): Assert => $page
            ->where('generations.data.0.status', 'queued')
            ->where('generations.data.0.is_active', true)
            ->where('generations.data.1.image_url', null)
            ->where('generations.data.1.image_expired', true));
});

it('returns only the watched generations that belong to the user', function (): void {
    $user = User::factory()->create();
    $mine = Generation::factory()->processing()->for($user)->create();
    $theirs = Generation::factory()->processing()->create();

    partialStudioReload($user, ['watched'], ['watch' => [$mine->uuid, $theirs->uuid]])
        ->assertOk()
        ->assertJsonCount(1, 'props.watched.data')
        ->assertJsonPath('props.watched.data.0.id', $mine->uuid)
        ->assertJsonMissingPath('props.generations');
});

it('starts a generation and holds its maximum price', function (): void {
    Queue::fake();
    $user = User::factory()->withCredits('1')->create();

    $this->actingAs($user)
        ->post(route('generations.store'), [
            'prompt' => 'A Japandi living room',
            'size' => '1536x1024',
            'quality' => 'high',
            'reference_images' => ['https://example.com/moodboard.jpg'],
        ])
        ->assertRedirect(route('generations.index'));

    $generation = $user->generations()->sole();

    expect($generation->status)->toBe(GenerationStatus::Pending)
        ->and($generation->reference_images)->toBe(['https://example.com/moodboard.jpg'])
        ->and((string) $generation->maximum_price)->toBe('0.457106250000');
    Queue::assertPushed(SubmitGenerationJob::class);
});

it('refuses a generation the user cannot afford', function (): void {
    Queue::fake();
    $user = User::factory()->withCredits('0.01')->create();

    $this->actingAs($user)
        ->from(route('generations.index'))
        ->post(route('generations.store'), ['prompt' => 'A loft', 'size' => '1024x1024', 'quality' => 'high'])
        ->assertRedirect(route('generations.index'))
        ->assertSessionHasErrors('credits');

    expect($user->generations()->count())->toBe(0);
    Queue::assertNothingPushed();
});

it('validates the request before spending anything', function (array $payload, string $field): void {
    $user = User::factory()->withCredits('10')->create();

    $this->actingAs($user)
        ->post(route('generations.store'), [
            'prompt' => 'A loft',
            'size' => '1024x1024',
            'quality' => 'medium',
            ...$payload,
        ])
        ->assertSessionHasErrors($field);

    expect($user->generations()->count())->toBe(0);
})->with([
    'missing prompt' => [['prompt' => ''], 'prompt'],
    'prompt too long' => [['prompt' => str_repeat('a', 4001)], 'prompt'],
    'unknown size' => [['size' => '512x512'], 'size'],
    'unknown quality' => [['quality' => 'ultra'], 'quality'],
    'too many references' => [['reference_images' => array_map(fn (int $i): string => "https://example.com/{$i}.png", range(1, 5))], 'reference_images'],
    'reference is not a url' => [['reference_images' => ['not a url']], 'reference_images.0'],
    'reference is not http' => [['reference_images' => ['ftp://example.com/a.png']], 'reference_images.0'],
    'duplicate references' => [['reference_images' => ['https://example.com/a.png', 'https://example.com/a.png']], 'reference_images.0'],
]);
