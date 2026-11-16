<?php

declare(strict_types=1);

namespace App\Data;

use BcMath\Number;

final readonly class SpendReportUserRow
{
    public function __construct(
        public int $userId,
        public string $name,
        public string $email,
        public int $generations,
        public Number $charged,
        public Number $providerCost,
    ) {
        //
    }

    public function profit(): Number
    {
        return $this->charged - $this->providerCost;
    }

    /**
     * @return array{user_id: int, name: string, email: string, generations: int, charged: string, provider_cost: string, profit: string}
     */
    public function toArray(): array
    {
        return [
            'user_id' => $this->userId,
            'name' => $this->name,
            'email' => $this->email,
            'generations' => $this->generations,
            'charged' => (string) $this->charged,
            'provider_cost' => (string) $this->providerCost,
            'profit' => (string) $this->profit(),
        ];
    }
}
