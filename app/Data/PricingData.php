<?php

declare(strict_types=1);

namespace App\Data;

use App\Enums\GenerationQuality;
use App\Enums\GenerationSize;
use App\Support\Decimal;
use BcMath\Number;
use Illuminate\Support\Fluent;

final readonly class PricingData
{
    /**
     * @param  array<string, array<string, int>>  $outputTokens
     * @param  array<string, array{min: int, max: int}>  $reasoningTokens
     */
    public function __construct(
        public Rates $rates,
        public int $charactersPerTextToken,
        public Number $textTokenVariance,
        public int $tokensPerReferenceImage,
        private array $outputTokens,
        private array $reasoningTokens,
    ) {
        //
    }

    /**
     * @param  array<array-key, mixed>  $pricing
     */
    public static function fromArray(array $pricing): self
    {
        $data = new Fluent($pricing);
        $outputTokens = [];
        $reasoningTokens = [];

        foreach (GenerationQuality::cases() as $quality) {
            foreach (GenerationSize::cases() as $size) {
                $outputTokens[$quality->value][$size->value] = $data->integer("tokens.output.{$quality->value}.{$size->value}");
            }

            $reasoningTokens[$quality->value] = [
                'min' => $data->integer("tokens.reasoning.{$quality->value}.min"),
                'max' => $data->integer("tokens.reasoning.{$quality->value}.max"),
            ];
        }

        return new self(
            rates: Rates::fromArray($data->array('rates')),
            charactersPerTextToken: $data->integer('tokens.characters_per_text_token'),
            textTokenVariance: Decimal::from($data->get('tokens.text_token_variance')),
            tokensPerReferenceImage: $data->integer('tokens.per_reference_image'),
            outputTokens: $outputTokens,
            reasoningTokens: $reasoningTokens,
        );
    }

    public function outputTokens(GenerationQuality $quality, GenerationSize $size): int
    {
        return $this->outputTokens[$quality->value][$size->value];
    }

    public function minimumReasoningTokens(GenerationQuality $quality): int
    {
        return $this->reasoningTokens[$quality->value]['min'];
    }

    public function maximumReasoningTokens(GenerationQuality $quality): int
    {
        return $this->reasoningTokens[$quality->value]['max'];
    }
}
