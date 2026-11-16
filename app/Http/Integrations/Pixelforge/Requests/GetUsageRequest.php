<?php

declare(strict_types=1);

namespace App\Http\Integrations\Pixelforge\Requests;

use App\Data\UsageDay;
use Carbon\CarbonImmutable;
use Illuminate\Support\Fluent;
use Override;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Http\Response;

final class GetUsageRequest extends Request
{
    protected Method $method = Method::GET;

    public function __construct(
        private readonly CarbonImmutable $startDate,
        private readonly CarbonImmutable $endDate,
    ) {
        //
    }

    public function resolveEndpoint(): string
    {
        return '/usage';
    }

    /**
     * @return list<UsageDay>
     */
    #[Override]
    public function createDtoFromResponse(Response $response): array
    {
        $days = new Fluent($response->array())->array('data');

        return array_values(array_map(
            UsageDay::fromArray(...),
            array_filter($days, is_array(...)),
        ));
    }

    /**
     * @return array{start_date: string, end_date: string}
     */
    protected function defaultQuery(): array
    {
        return [
            'start_date' => $this->startDate->toDateString(),
            'end_date' => $this->endDate->toDateString(),
        ];
    }
}
