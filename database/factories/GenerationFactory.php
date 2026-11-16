<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\GenerationQuality;
use App\Enums\GenerationSize;
use App\Enums\GenerationStatus;
use App\Models\Generation;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Generation>
 */
final class GenerationFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'status' => GenerationStatus::Pending,
            'prompt' => fake()->sentence(),
            'size' => GenerationSize::Square,
            'quality' => GenerationQuality::Medium,
            'reference_images' => [],
            'estimated_price' => '0.083906250000',
            'maximum_price' => '0.087500000000',
        ];
    }

    public function queued(): self
    {
        return $this->state(fn (): array => [
            'status' => GenerationStatus::Queued,
            'pixelforge_id' => 'gen_'.fake()->unique()->regexify('[0-9A-Z]{26}'),
            'rates' => [
                'input_text_tokens' => '5.00',
                'input_image_tokens' => '10.00',
                'output_tokens' => '40.00',
                'reasoning_tokens' => '12.50',
            ],
            'submitted_at' => now(),
        ]);
    }

    public function processing(): self
    {
        return $this->queued()->state(fn (): array => [
            'status' => GenerationStatus::Processing,
            'started_at' => now(),
        ]);
    }

    public function succeeded(): self
    {
        return $this->processing()->state(fn (): array => [
            'status' => GenerationStatus::Succeeded,
            'input_text_tokens' => 3,
            'input_image_tokens' => 0,
            'output_tokens' => 1100,
            'reasoning_tokens' => 1000,
            'provider_cost' => '0.056515000000',
            'price' => '0.070643750000',
            'image_url' => 'https://pixelforge.promptedit.dev/images/example.png',
            'image_url_expires_at' => now()->addDay(),
            'completed_at' => now(),
        ]);
    }

    public function failed(): self
    {
        return $this->processing()->state(fn (): array => [
            'status' => GenerationStatus::Failed,
            'provider_cost' => '0',
            'price' => '0',
            'error_code' => 'generation_failed',
            'error_message' => 'The model could not produce an image for this prompt.',
            'completed_at' => now(),
        ]);
    }
}
