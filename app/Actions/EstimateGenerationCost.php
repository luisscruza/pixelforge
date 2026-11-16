<?php

declare(strict_types=1);

namespace App\Actions;

use App\Data\CostEstimate;
use App\Data\GenerationInput;
use App\Data\PricingData;
use App\Data\TokenUsage;
use BcMath\Number;

final readonly class EstimateGenerationCost
{
    public function __construct(
        private CalculateProviderCost $calculateProviderCost,
        private ApplyMarkup $applyMarkup,
    ) {
        //
    }

    public function handle(GenerationInput $input, PricingData $pricing): CostEstimate
    {
        $textTokens = intdiv(mb_strlen($input->prompt) + $pricing->charactersPerTextToken - 1, $pricing->charactersPerTextToken);
        $imageTokens = count($input->referenceImages) * $pricing->tokensPerReferenceImage;
        $outputTokens = $pricing->outputTokens($input->quality, $input->size);
        $minimumReasoning = $pricing->minimumReasoningTokens($input->quality);
        $maximumReasoning = $pricing->maximumReasoningTokens($input->quality);

        $expectedUsage = new TokenUsage(
            inputText: $textTokens,
            inputImage: $imageTokens,
            output: $outputTokens,
            reasoning: intdiv($minimumReasoning + $maximumReasoning, 2),
        );

        $maximumUsage = new TokenUsage(
            inputText: (int) (string) (new Number(1) + $pricing->textTokenVariance)->mul($textTokens)->ceil(),
            inputImage: $imageTokens,
            output: $outputTokens,
            reasoning: $maximumReasoning,
        );

        $expectedProviderCost = $this->calculateProviderCost->handle($expectedUsage, $pricing->rates);
        $maximumProviderCost = $this->calculateProviderCost->handle($maximumUsage, $pricing->rates);

        return new CostEstimate(
            expectedProviderCost: $expectedProviderCost,
            maximumProviderCost: $maximumProviderCost,
            expectedPrice: $this->applyMarkup->handle($expectedProviderCost),
            maximumPrice: $this->applyMarkup->handle($maximumProviderCost),
        );
    }
}
