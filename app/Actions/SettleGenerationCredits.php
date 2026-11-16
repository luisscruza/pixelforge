<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\CreditTransactionType;
use App\Enums\GenerationStatus;
use App\Models\CreditTransaction;
use App\Models\Generation;
use BcMath\Number;

final readonly class SettleGenerationCredits
{
    public function handle(Generation $generation): void
    {
        if (! $generation->status->isFinal()) {
            return;
        }

        $hold = CreditTransaction::query()
            ->whereBelongsTo($generation)
            ->where('type', CreditTransactionType::Hold)
            ->first();

        if ($hold instanceof CreditTransaction) {
            $this->record($generation, CreditTransactionType::Release, $hold->amount->mul(-1));
        }

        if ($generation->status === GenerationStatus::Succeeded && $generation->price !== null) {
            $this->record($generation, CreditTransactionType::Charge, $generation->price->mul(-1));
        }
    }

    private function record(Generation $generation, CreditTransactionType $type, Number $amount): void
    {
        CreditTransaction::query()->firstOrCreate(
            ['generation_id' => $generation->id, 'type' => $type],
            ['user_id' => $generation->user_id, 'amount' => $amount],
        );
    }
}
