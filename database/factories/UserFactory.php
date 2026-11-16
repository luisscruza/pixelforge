<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\CreditTransactionType;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
final class UserFactory extends Factory
{
    private static string $password;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => self::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
        ];
    }

    public function unverified(): static
    {
        return $this->state(fn (array $attributes): array => [
            'email_verified_at' => null,
        ]);
    }

    public function admin(): self
    {
        return $this->state(fn (): array => ['is_admin' => true]);
    }

    public function withCredits(string $amount = '10'): self
    {
        return $this->afterCreating(function (User $user) use ($amount): void {
            $user->creditTransactions()->create([
                'type' => CreditTransactionType::Purchase,
                'amount' => $amount,
            ]);
        });
    }
}
