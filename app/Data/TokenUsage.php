<?php

declare(strict_types=1);

namespace App\Data;

use Illuminate\Support\Fluent;

final readonly class TokenUsage
{
    public function __construct(
        public int $inputText,
        public int $inputImage,
        public int $output,
        public int $reasoning,
    ) {
        //
    }

    /**
     * @param  array<array-key, mixed>  $usage
     */
    public static function fromArray(array $usage): self
    {
        $data = new Fluent($usage);

        return new self(
            inputText: $data->integer('input_text_tokens'),
            inputImage: $data->integer('input_image_tokens'),
            output: $data->integer('output_tokens'),
            reasoning: $data->integer('reasoning_tokens'),
        );
    }

    public function total(): int
    {
        return $this->inputText + $this->inputImage + $this->output + $this->reasoning;
    }
}
