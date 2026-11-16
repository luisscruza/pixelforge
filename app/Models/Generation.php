<?php

declare(strict_types=1);

namespace App\Models;

use App\Casts\AsNumber;
use App\Data\Rates;
use App\Data\TokenUsage;
use App\Enums\GenerationQuality;
use App\Enums\GenerationSize;
use App\Enums\GenerationStatus;
use App\Enums\SubmissionStatus;
use BcMath\Number;
use Carbon\CarbonImmutable;
use Database\Factories\GenerationFactory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property string $uuid
 * @property int $user_id
 * @property string|null $pixelforge_id
 * @property GenerationStatus $status
 * @property SubmissionStatus $submission_status
 * @property string $prompt
 * @property GenerationSize $size
 * @property GenerationQuality $quality
 * @property list<string> $reference_images
 * @property array<string, string>|null $rates
 * @property int|null $input_text_tokens
 * @property int|null $input_image_tokens
 * @property int|null $output_tokens
 * @property int|null $reasoning_tokens
 * @property Number $estimated_price
 * @property Number $maximum_price
 * @property Number|null $provider_cost
 * @property Number|null $price
 * @property string|null $error_code
 * @property string|null $error_message
 * @property string|null $image_url
 * @property CarbonImmutable|null $image_url_expires_at
 * @property int $submission_attempts
 * @property CarbonImmutable|null $submitted_at
 * @property CarbonImmutable|null $started_at
 * @property CarbonImmutable|null $completed_at
 * @property CarbonImmutable|null $created_at
 * @property CarbonImmutable|null $updated_at
 * @property-read User $user
 * @property-read Collection<int, CreditTransaction> $creditTransactions
 */
final class Generation extends Model
{
    /** @use HasFactory<GenerationFactory> */
    use HasFactory;

    use HasUuids;

    /**
     * @return list<string>
     */
    public function uniqueIds(): array
    {
        return ['uuid'];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return HasMany<CreditTransaction, $this>
     */
    public function creditTransactions(): HasMany
    {
        return $this->hasMany(CreditTransaction::class);
    }

    public function usage(): ?TokenUsage
    {
        if ($this->input_text_tokens === null || $this->input_image_tokens === null || $this->output_tokens === null || $this->reasoning_tokens === null) {
            return null;
        }

        return new TokenUsage(
            inputText: $this->input_text_tokens,
            inputImage: $this->input_image_tokens,
            output: $this->output_tokens,
            reasoning: $this->reasoning_tokens,
        );
    }

    public function billedRates(): ?Rates
    {
        return $this->rates === null ? null : Rates::fromArray($this->rates);
    }

    /**
     * @return array<string, string|class-string>
     */
    protected function casts(): array
    {
        return [
            'status' => GenerationStatus::class,
            'submission_status' => SubmissionStatus::class,
            'size' => GenerationSize::class,
            'quality' => GenerationQuality::class,
            'reference_images' => 'array',
            'rates' => 'array',
            'estimated_price' => AsNumber::class,
            'maximum_price' => AsNumber::class,
            'provider_cost' => AsNumber::class,
            'price' => AsNumber::class,
            'image_url_expires_at' => 'datetime',
            'submitted_at' => 'datetime',
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }
}
