<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\CreditTransactionType;
use App\Models\CreditTransaction;
use App\Models\User;
use BcMath\Number;

final readonly class PurchaseCredits
{
    public function handle(User $user, Number $amount): CreditTransaction
    {
        return $user->creditTransactions()->create([
            'type' => CreditTransactionType::Purchase,
            'amount' => $amount,
        ]);
    }
}
