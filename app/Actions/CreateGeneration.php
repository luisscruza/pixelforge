<?php

declare(strict_types=1);

namespace App\Actions;

use App\Data\CostEstimate;
use App\Data\GenerationInput;
use App\Enums\GenerationStatus;
use App\Jobs\SubmitGenerationJob;
use App\Models\Generation;
use App\Models\User;
use Illuminate\Support\Facades\DB;

final readonly class CreateGeneration
{
    public function handle(User $user, GenerationInput $input, CostEstimate $estimate): Generation
    {
        $generation = DB::transaction(fn (): Generation => $user->generations()->create([
            'status' => GenerationStatus::Pending,
            'prompt' => $input->prompt,
            'size' => $input->size,
            'quality' => $input->quality,
            'reference_images' => $input->referenceImages,
            'estimated_price' => $estimate->expectedPrice,
            'maximum_price' => $estimate->maximumPrice,
        ]));

        dispatch(new SubmitGenerationJob($generation))->afterCommit();

        return $generation;
    }
}
