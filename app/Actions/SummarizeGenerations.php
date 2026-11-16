<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\GenerationStatus;
use App\Models\User;
use App\Support\Decimal;

final readonly class SummarizeGenerations
{
    /**
     * @return array{total: int, succeeded: int, in_flight: int, spent: string}
     */
    public function handle(User $user): array
    {
        $generations = $user->generations();

        return [
            'total' => $generations->clone()->count(),
            'succeeded' => $generations->clone()->where('status', GenerationStatus::Succeeded)->count(),
            'in_flight' => $generations->clone()->whereIn('status', [GenerationStatus::Pending, GenerationStatus::Queued, GenerationStatus::Processing])->count(),
            'spent' => (string) Decimal::from($generations->clone()->where('status', GenerationStatus::Succeeded)->sum('price')),
        ];
    }
}
