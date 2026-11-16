<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\WebhookEvent;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<WebhookEvent>
 */
final class WebhookEventFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $generationId = 'gen_'.fake()->unique()->regexify('[0-9A-Z]{26}');

        return [
            'id' => 'evt_'.fake()->unique()->regexify('[0-9A-Z]{26}'),
            'type' => 'generation.processing',
            'pixelforge_generation_id' => $generationId,
            'payload' => ['data' => ['object' => ['id' => $generationId, 'status' => 'processing']]],
        ];
    }
}
