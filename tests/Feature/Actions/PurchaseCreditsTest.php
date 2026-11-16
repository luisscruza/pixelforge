<?php

declare(strict_types=1);

use App\Actions\GetCreditBalance;
use App\Actions\PurchaseCredits;
use App\Models\User;
use BcMath\Number;

it('adds purchased credits to the balance', function (): void {
    $user = User::factory()->withCredits('2.5')->create();

    resolve(PurchaseCredits::class)->handle($user, new Number('10'));

    expect((string) resolve(GetCreditBalance::class)->handle($user))->toBe('12.500000000000');
});

it('starts new users at zero', function (): void {
    expect(resolve(GetCreditBalance::class)->handle(User::factory()->create())->compare(0))->toBe(0);
});

it('keeps balances separate per user', function (): void {
    $alice = User::factory()->withCredits('5')->create();
    User::factory()->withCredits('7')->create();

    expect((string) resolve(GetCreditBalance::class)->handle($alice))->toBe('5.000000000000');
});
