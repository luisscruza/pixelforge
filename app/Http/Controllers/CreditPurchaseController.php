<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\PurchaseCredits;
use App\Http\Requests\StoreCreditPurchaseRequest;
use App\Models\User;
use Illuminate\Container\Attributes\CurrentUser;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;

final class CreditPurchaseController
{
    public function store(StoreCreditPurchaseRequest $request, #[CurrentUser] User $user, PurchaseCredits $purchaseCredits): RedirectResponse
    {
        $purchaseCredits->handle($user, $request->amount());

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Added $'.$request->integer('amount').' in credits.']);

        return back();
    }
}
