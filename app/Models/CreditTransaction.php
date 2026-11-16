<?php

declare(strict_types=1);

namespace App\Models;

use App\Casts\AsNumber;
use App\Enums\CreditTransactionType;
use BcMath\Number;
use Carbon\CarbonImmutable;
use Database\Factories\CreditTransactionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $user_id
 * @property int|null $generation_id
 * @property CreditTransactionType $type
 * @property Number $amount
 * @property CarbonImmutable|null $created_at
 * @property CarbonImmutable|null $updated_at
 * @property-read User $user
 * @property-read Generation|null $generation
 */
final class CreditTransaction extends Model
{
    /** @use HasFactory<CreditTransactionFactory> */
    use HasFactory;

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return BelongsTo<Generation, $this>
     */
    public function generation(): BelongsTo
    {
        return $this->belongsTo(Generation::class);
    }

    /**
     * @return array<string, string|class-string>
     */
    protected function casts(): array
    {
        return [
            'type' => CreditTransactionType::class,
            'amount' => AsNumber::class,
        ];
    }
}
