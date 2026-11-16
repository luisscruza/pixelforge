<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Carbon\CarbonImmutable;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

final class ShowSpendReportRequest extends FormRequest
{
    public const int MAX_DAYS = 92;

    private const int DEFAULT_DAYS = 14;

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'start_date' => ['nullable', 'date_format:Y-m-d'],
            'end_date' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:start_date'],
        ];
    }

    /**
     * @return list<callable(Validator): void>
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if ($validator->errors()->isNotEmpty()) {
                    return;
                }

                if ($this->startDate()->diffInDays($this->endDate()) + 1 > self::MAX_DAYS) {
                    $validator->errors()->add('end_date', 'The report can cover at most '.self::MAX_DAYS.' days.');
                }
            },
        ];
    }

    public function startDate(): CarbonImmutable
    {
        return $this->filled('start_date')
            ? CarbonImmutable::createFromFormat('Y-m-d', $this->string('start_date')->value(), 'UTC')?->startOfDay() ?? $this->endDate()
            : $this->endDate()->subDays(self::DEFAULT_DAYS - 1);
    }

    public function endDate(): CarbonImmutable
    {
        return $this->filled('end_date')
            ? CarbonImmutable::createFromFormat('Y-m-d', $this->string('end_date')->value(), 'UTC')?->startOfDay() ?? today()
            : now()->utc()->startOfDay();
    }
}
