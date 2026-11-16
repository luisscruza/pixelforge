<?php

declare(strict_types=1);

namespace App\Data;

use BcMath\Number;

final readonly class SpendReportDay
{
    /**
     * @param  list<SpendReportUserRow>  $users
     */
    public function __construct(
        public string $date,
        public array $users,
        public ?Number $pixelforgeCost,
    ) {
        //
    }

    public function generations(): int
    {
        return array_sum(array_map(fn (SpendReportUserRow $row): int => $row->generations, $this->users));
    }

    public function charged(): Number
    {
        return array_reduce($this->users, fn (Number $sum, SpendReportUserRow $row): Number => $sum + $row->charged, new Number(0));
    }

    public function providerCost(): Number
    {
        return array_reduce($this->users, fn (Number $sum, SpendReportUserRow $row): Number => $sum + $row->providerCost, new Number(0));
    }

    public function difference(): ?Number
    {
        return $this->pixelforgeCost instanceof Number ? $this->providerCost() - $this->pixelforgeCost : null;
    }

    /**
     * @return array{date: string, generations: int, charged: string, provider_cost: string, profit: string, pixelforge_cost: string|null, difference: string|null, users: list<array<string, int|string>>}
     */
    public function toArray(): array
    {
        $difference = $this->difference();

        return [
            'date' => $this->date,
            'generations' => $this->generations(),
            'charged' => (string) $this->charged(),
            'provider_cost' => (string) $this->providerCost(),
            'profit' => (string) ($this->charged() - $this->providerCost()),
            'pixelforge_cost' => $this->pixelforgeCost instanceof Number ? (string) $this->pixelforgeCost : null,
            'difference' => $difference instanceof Number ? (string) $difference : null,
            'users' => array_map(fn (SpendReportUserRow $row): array => $row->toArray(), $this->users),
        ];
    }
}
