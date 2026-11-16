<?php

declare(strict_types=1);

namespace App\Http\Integrations\Pixelforge\Requests;

use App\Data\PricingData;
use Override;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Http\Response;

final class GetPricingRequest extends Request
{
    protected Method $method = Method::GET;

    public function resolveEndpoint(): string
    {
        return '/pricing';
    }

    #[Override]
    public function createDtoFromResponse(Response $response): PricingData
    {
        return PricingData::fromArray($response->array());
    }
}
