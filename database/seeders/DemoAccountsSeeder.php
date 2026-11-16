<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Actions\PurchaseCredits;
use App\Models\User;
use BcMath\Number;
use Illuminate\Database\Seeder;

final class DemoAccountsSeeder extends Seeder
{
    public const string PASSWORD = 'interioria-demo';

    /**
     * @var list<array{name: string, email: string, is_admin: bool, credits: int}>
     */
    private const array ACCOUNTS = [
        ['name' => 'Studio Admin', 'email' => 'admin@interioria.test', 'is_admin' => true, 'credits' => 25],
        ['name' => 'Demo Designer', 'email' => 'designer@interioria.test', 'is_admin' => false, 'credits' => 10],
    ];

    public function run(PurchaseCredits $purchaseCredits): void
    {
        foreach (self::ACCOUNTS as $account) {
            $user = User::query()->firstOrCreate(
                ['email' => $account['email']],
                [
                    'name' => $account['name'],
                    'password' => self::PASSWORD,
                    'email_verified_at' => now(),
                ],
            );

            $user->update(['is_admin' => $account['is_admin']]);

            if ($user->wasRecentlyCreated) {
                $purchaseCredits->handle($user, new Number($account['credits']));
            }
        }
    }
}
