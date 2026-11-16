<?php

declare(strict_types=1);

use App\Actions\GetCreditBalance;
use App\Models\User;
use Database\Seeders\DemoAccountsSeeder;
use Illuminate\Support\Facades\Hash;

it('creates an admin and a regular demo account with credits', function (): void {
    $this->seed(DemoAccountsSeeder::class);

    $admin = User::query()->where('email', 'admin@interioria.test')->sole();
    $designer = User::query()->where('email', 'designer@interioria.test')->sole();

    expect($admin->is_admin)->toBeTrue()
        ->and($admin->can('viewSpendReport'))->toBeTrue()
        ->and($designer->is_admin)->toBeFalse()
        ->and(Hash::check(DemoAccountsSeeder::PASSWORD, $admin->password))->toBeTrue()
        ->and((string) resolve(GetCreditBalance::class)->handle($admin))->toBe('25.000000000000')
        ->and((string) resolve(GetCreditBalance::class)->handle($designer))->toBe('10.000000000000');
});

it('can run again without duplicating accounts or credits', function (): void {
    $this->seed(DemoAccountsSeeder::class);
    $this->seed(DemoAccountsSeeder::class);

    $admin = User::query()->where('email', 'admin@interioria.test')->sole();

    expect(User::query()->count())->toBe(2)
        ->and((string) resolve(GetCreditBalance::class)->handle($admin))->toBe('25.000000000000');
});

it('lets the demo admin open the spend report', function (): void {
    $this->seed(DemoAccountsSeeder::class);

    $this->post(route('login.store'), [
        'email' => 'admin@interioria.test',
        'password' => DemoAccountsSeeder::PASSWORD,
    ])->assertRedirect();

    $this->get(route('admin.spend-report.index'))->assertOk();
});
