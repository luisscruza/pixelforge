<?php

declare(strict_types=1);

namespace App\Actions;

use App\Data\CostEstimate;
use App\Data\GenerationInput;
use App\Enums\CreditTransactionType;
use App\Enums\GenerationStatus;
use App\Exceptions\InsufficientCreditsException;
use App\Jobs\SubmitGenerationJob;
use App\Models\Generation;
use App\Models\User;
use Illuminate\Support\Facades\DB;

final readonly class CreateGeneration
{
    public function __construct(
        private GetCreditBalance $getCreditBalance,
    ) {
        //
    }

    public function handle(User $user, GenerationInput $input, CostEstimate $estimate): Generation
    {
        $generation = DB::transaction(function () use ($user, $input, $estimate): Generation {
            User::query()->lockForUpdate()->findOrFail($user->id);

            $balance = $this->getCreditBalance->handle($user);

            throw_if($balance < $estimate->maximumPrice, InsufficientCreditsException::class, $balance, $estimate->maximumPrice);

            $generation = $user->generations()->create([
                'status' => GenerationStatus::Pending,
                'prompt' => $input->prompt,
                'size' => $input->size,
                'quality' => $input->quality,
                'reference_images' => $input->referenceImages,
                'estimated_price' => $estimate->expectedPrice,
                'maximum_price' => $estimate->maximumPrice,
            ]);

            $generation->creditTransactions()->create([
                'user_id' => $user->id,
                'type' => CreditTransactionType::Hold,
                'amount' => $estimate->maximumPrice->mul(-1),
            ]);

            return $generation;
        });

        dispatch(new SubmitGenerationJob($generation))->afterCommit();

        return $generation;
    }
}
