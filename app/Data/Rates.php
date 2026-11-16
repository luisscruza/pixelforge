<?php

declare(strict_types=1);

namespace App\Data;

use App\Support\Decimal;
use BcMath\Number;
use Illuminate\Support\Fluent;

final readonly class Rates
{
    public function __construct(
        public Number $inputText,
        public Number $inputImage,
        public Number $output,
        public Number $reasoning,
    ) {
        //
    }

    /**
     * @param  array<array-key, mixed>  $rates
     */
    public static function fromArray(array $rates): self
    {
        $data = new Fluent($rates);

        return new self(
            inputText: Decimal::from($data->get('input_text_tokens')),
            inputImage: Decimal::from($data->get('input_image_tokens')),
            output: Decimal::from($data->get('output_tokens')),
            reasoning: Decimal::from($data->get('reasoning_tokens')),
        );
    }

    /**
     * @return array{input_text_tokens: string, input_image_tokens: string, output_tokens: string, reasoning_tokens: string}
     */
    public function toArray(): array
    {
        return [
            'input_text_tokens' => (string) $this->inputText,
            'input_image_tokens' => (string) $this->inputImage,
            'output_tokens' => (string) $this->output,
            'reasoning_tokens' => (string) $this->reasoning,
        ];
    }
}
