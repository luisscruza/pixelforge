<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\GenerationStatus;
use App\Models\Generation;
use BcMath\Number;
use Illuminate\Support\Facades\DB;

final readonly class MarkGenerationAsFailed
{
    public function __construct(
        private SettleGenerationCredits $settleCredits,
    ) {
        //
    }

    public function handle(Generation $generation, string $errorCode, string $errorMessage): void
    {
        DB::transaction(function () use ($generation, $errorCode, $errorMessage): void {
            $locked = Generation::query()->lockForUpdate()->find($generation->id);

            if (! $locked instanceof Generation || $locked->status->isFinal()) {
                return;
            }

            $locked->update([
                'status' => GenerationStatus::Failed,
                'provider_cost' => new Number(0),
                'price' => new Number(0),
                'error_code' => $errorCode,
                'error_message' => $errorMessage,
                'completed_at' => now(),
            ]);

            $this->settleCredits->handle($locked);
        });
    }
}
