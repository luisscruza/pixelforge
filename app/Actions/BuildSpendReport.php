<?php

declare(strict_types=1);

namespace App\Actions;

use App\Data\SpendReport;
use App\Data\SpendReportDay;
use App\Data\SpendReportUserRow;
use App\Enums\GenerationStatus;
use App\Enums\SubmissionStatus;
use App\Http\Integrations\Pixelforge\PixelforgeConnector;
use App\Http\Integrations\Pixelforge\PixelforgeException;
use App\Models\Generation;
use App\Support\Decimal;
use BcMath\Number;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Fluent;
use Saloon\Exceptions\Request\FatalRequestException;

final readonly class BuildSpendReport
{
    public function __construct(
        private PixelforgeConnector $pixelforge,
    ) {
        //
    }

    public function handle(CarbonImmutable $startDate, CarbonImmutable $endDate): SpendReport
    {
        $startDate = $startDate->startOfDay();
        $endDate = $endDate->startOfDay();
        $rowsByDate = $this->rowsByDate($startDate, $endDate);
        $usage = $this->pixelforgeUsage($startDate, $endDate);

        $days = [];

        for ($day = $startDate; $day <= $endDate; $day = $day->addDay()) {
            $date = $day->toDateString();

            $days[] = new SpendReportDay(
                date: $date,
                users: $rowsByDate[$date] ?? [],
                pixelforgeCost: $usage === null ? null : ($usage[$date] ?? new Number(0)),
            );
        }

        return new SpendReport(
            startDate: $startDate,
            endDate: $endDate,
            days: $days,
            userTotals: $this->userTotals($rowsByDate),
            usageAvailable: $usage !== null,
            unresolvedSubmissions: Generation::query()
                ->where('status', GenerationStatus::Pending)
                ->where('submission_status', SubmissionStatus::NeedsReconciliation)
                ->count(),
        );
    }

    /**
     * @return array<string, list<SpendReportUserRow>>
     */
    private function rowsByDate(CarbonImmutable $startDate, CarbonImmutable $endDate): array
    {
        $records = DB::table('generations')
            ->join('users', 'users.id', '=', 'generations.user_id')
            ->where('generations.status', GenerationStatus::Succeeded->value)
            ->where('generations.completed_at', '>=', $startDate)
            ->where('generations.completed_at', '<', $endDate->addDay())
            ->selectRaw('generations.completed_at::date as date, users.id as user_id, users.name, users.email')
            ->selectRaw('count(*) as generations, sum(generations.price) as charged, sum(generations.provider_cost) as provider_cost')
            ->groupByRaw('generations.completed_at::date, users.id, users.name, users.email')
            ->orderBy('date')
            ->orderBy('users.name')
            ->get();

        $rowsByDate = [];

        foreach ($records as $record) {
            $data = new Fluent((array) $record);

            $rowsByDate[$data->string('date')->value()][] = new SpendReportUserRow(
                userId: $data->integer('user_id'),
                name: $data->string('name')->value(),
                email: $data->string('email')->value(),
                generations: $data->integer('generations'),
                charged: Decimal::from($data->get('charged')),
                providerCost: Decimal::from($data->get('provider_cost')),
            );
        }

        return $rowsByDate;
    }

    /**
     * @param  array<string, list<SpendReportUserRow>>  $rowsByDate
     * @return list<SpendReportUserRow>
     */
    private function userTotals(array $rowsByDate): array
    {
        $totals = [];

        foreach ($rowsByDate as $rows) {
            foreach ($rows as $row) {
                $total = $totals[$row->userId] ?? null;

                $totals[$row->userId] = new SpendReportUserRow(
                    userId: $row->userId,
                    name: $row->name,
                    email: $row->email,
                    generations: ($total->generations ?? 0) + $row->generations,
                    charged: ($total->charged ?? new Number(0)) + $row->charged,
                    providerCost: ($total->providerCost ?? new Number(0)) + $row->providerCost,
                );
            }
        }

        usort($totals, fn (SpendReportUserRow $a, SpendReportUserRow $b): int => $b->charged <=> $a->charged);

        return $totals;
    }

    /**
     * @return array<string, Number>|null
     */
    private function pixelforgeUsage(CarbonImmutable $startDate, CarbonImmutable $endDate): ?array
    {
        try {
            $usage = [];

            foreach ($this->pixelforge->usage($startDate, $endDate) as $day) {
                $usage[$day->date] = $day->cost;
            }

            return $usage;
        } catch (PixelforgeException|FatalRequestException) {
            return null;
        }
    }
}
