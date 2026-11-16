<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Actions\BuildSpendReport;
use App\Http\Requests\ShowSpendReportRequest;
use Inertia\Inertia;
use Inertia\Response;

final class SpendReportController
{
    public function index(ShowSpendReportRequest $request, BuildSpendReport $buildSpendReport): Response
    {
        return Inertia::render('admin/SpendReport', [
            'filters' => [
                'start_date' => $request->startDate()->toDateString(),
                'end_date' => $request->endDate()->toDateString(),
            ],
            'report' => Inertia::defer(fn (): array => $buildSpendReport->handle($request->startDate(), $request->endDate())->toArray(), rescue: true),
            'maxDays' => ShowSpendReportRequest::MAX_DAYS,
        ]);
    }
}
