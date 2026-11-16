<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\User;
use App\Support\Decimal;
use BcMath\Number;

final readonly class GetCreditBalance
{
    public function handle(User $user): Number
    {
        return Decimal::from($user->creditTransactions()->sum('amount'));
    }
}
