<?php

declare(strict_types=1);

namespace App\Actions;

use App\Data\GenerationInput;
use App\Models\Generation;
use App\Models\User;

final readonly class StartGeneration
{
    public function __construct(
        private GetPricing $getPricing,
        private EstimateGenerationCost $estimateGenerationCost,
        private CreateGeneration $createGeneration,
    ) {
        //
    }

    public function handle(User $user, GenerationInput $input): Generation
    {
        $estimate = $this->estimateGenerationCost->handle($input, $this->getPricing->handle());

        return $this->createGeneration->handle($user, $input, $estimate);
    }
}
