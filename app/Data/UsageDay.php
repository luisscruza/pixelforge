<?php

declare(strict_types=1);

namespace App\Data;

use App\Support\Decimal;
use BcMath\Number;
use Illuminate\Support\Fluent;

final readonly class UsageDay
{
    public function __construct(
        public string $date,
        public int $generations,
        public TokenUsage $usage,
        public Number $cost,
    ) {
        //
    }

    /**
     * @param  array<array-key, mixed>  $day
     */
    public static function fromArray(array $day): self
    {
        $data = new Fluent($day);

        return new self(
            date: $data->string('date')->value(),
            generations: $data->integer('generations'),
            usage: TokenUsage::fromArray($day),
            cost: Decimal::from($data->get('cost_usd')),
        );
    }
}
