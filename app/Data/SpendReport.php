<?php

declare(strict_types=1);

namespace App\Data;

use BcMath\Number;
use Carbon\CarbonImmutable;

final readonly class SpendReport
{
    /**
     * @param  list<SpendReportDay>  $days
     * @param  list<SpendReportUserRow>  $userTotals
     */
    public function __construct(
        public CarbonImmutable $startDate,
        public CarbonImmutable $endDate,
        public array $days,
        public array $userTotals,
        public bool $usageAvailable,
        public int $unresolvedSubmissions,
    ) {
        //
    }

    public function charged(): Number
    {
        return array_reduce($this->days, fn (Number $sum, SpendReportDay $day): Number => $sum + $day->charged(), new Number(0));
    }

    public function providerCost(): Number
    {
        return array_reduce($this->days, fn (Number $sum, SpendReportDay $day): Number => $sum + $day->providerCost(), new Number(0));
    }

    public function pixelforgeCost(): ?Number
    {
        if (! $this->usageAvailable) {
            return null;
        }

        return array_reduce($this->days, fn (Number $sum, SpendReportDay $day): Number => $sum + ($day->pixelforgeCost ?? new Number(0)), new Number(0));
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $pixelforgeCost = $this->pixelforgeCost();

        return [
            'start_date' => $this->startDate->toDateString(),
            'end_date' => $this->endDate->toDateString(),
            'usage_available' => $this->usageAvailable,
            'unresolved_submissions' => $this->unresolvedSubmissions,
            'totals' => [
                'charged' => (string) $this->charged(),
                'provider_cost' => (string) $this->providerCost(),
                'profit' => (string) ($this->charged() - $this->providerCost()),
                'pixelforge_cost' => $pixelforgeCost instanceof Number ? (string) $pixelforgeCost : null,
                'difference' => $pixelforgeCost instanceof Number ? (string) ($this->providerCost() - $pixelforgeCost) : null,
            ],
            'days' => array_map(fn (SpendReportDay $day): array => $day->toArray(), $this->days),
            'user_totals' => array_map(fn (SpendReportUserRow $row): array => $row->toArray(), $this->userTotals),
        ];
    }
}
