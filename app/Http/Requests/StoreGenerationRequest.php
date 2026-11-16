<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Data\GenerationInput;
use App\Enums\GenerationQuality;
use App\Enums\GenerationSize;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class StoreGenerationRequest extends FormRequest
{
    public const int MAX_REFERENCE_IMAGES = 4;

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'prompt' => ['required', 'string', 'max:4000'],
            'size' => ['required', Rule::enum(GenerationSize::class)],
            'quality' => ['required', Rule::enum(GenerationQuality::class)],
            'reference_images' => ['array', 'max:'.self::MAX_REFERENCE_IMAGES],
            'reference_images.*' => ['required', 'string', 'distinct', 'max:2048', 'url:http,https'],
        ];
    }

    public function toInput(): GenerationInput
    {
        return new GenerationInput(
            prompt: $this->string('prompt')->value(),
            size: $this->enum('size', GenerationSize::class) ?? GenerationSize::Square,
            quality: $this->enum('quality', GenerationQuality::class) ?? GenerationQuality::Medium,
            referenceImages: array_values(array_filter(
                (array) $this->input('reference_images', []),
                is_string(...),
            )),
        );
    }
}
