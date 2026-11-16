<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Enums\GenerationStatus;
use App\Enums\SubmissionStatus;
use App\Models\Generation;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Generation
 */
final class GenerationResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $imageAvailable = $this->image_url !== null && $this->image_url_expires_at?->isFuture() === true;

        return [
            'id' => $this->uuid,
            'prompt' => $this->prompt,
            'size' => $this->size->value,
            'quality' => $this->quality->value,
            'reference_images' => $this->reference_images,
            'status' => $this->status === GenerationStatus::Pending ? GenerationStatus::Queued->value : $this->status->value,
            'is_active' => ! $this->status->isFinal(),
            'is_confirming' => $this->status === GenerationStatus::Pending
                && in_array($this->submission_status, [SubmissionStatus::Unknown, SubmissionStatus::NeedsReconciliation], true),
            'estimated_price' => (string) $this->estimated_price,
            'maximum_price' => (string) $this->maximum_price,
            'price' => $this->price === null ? null : (string) $this->price,
            'tokens' => $this->usage()?->total(),
            'image_url' => $imageAvailable ? $this->image_url : null,
            'image_expired' => $this->image_url !== null && ! $imageAvailable,
            'error_message' => $this->error_message,
            'created_at' => $this->created_at?->toIso8601ZuluString(),
            'completed_at' => $this->completed_at?->toIso8601ZuluString(),
        ];
    }
}
