<?php

declare(strict_types=1);

use App\Actions\GetCreditBalance;
use App\Models\User;
use Inertia\Testing\AssertableInertia;

it('adds a credit pack to the balance', function (): void {
    $user = User::factory()->withCredits('1')->create();

    $this->actingAs($user)
        ->from(route('generations.index'))
        ->post(route('credit-purchases.store'), ['amount' => 10])
        ->assertRedirect(route('generations.index'));

    expect((string) resolve(GetCreditBalance::class)->handle($user))->toBe('11.000000000000');
});

it('only sells the advertised packs', function (mixed $amount): void {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post(route('credit-purchases.store'), ['amount' => $amount])
        ->assertSessionHasErrors('amount');

    expect($user->creditTransactions()->count())->toBe(0);
})->with([7, -5, 1000000, 'ten', null]);

it('requires an account', function (): void {
    $this->post(route('credit-purchases.store'), ['amount' => 5])->assertRedirect(route('login'));
});

it('shares the balance with every page', function (): void {
    $user = User::factory()->withCredits('2.5')->create();

    $this->actingAs($user)
        ->get(route('profile.edit'))
        ->assertInertia(fn (AssertableInertia $page): AssertableInertia => $page->where('credits', '2.500000000000'));
});
