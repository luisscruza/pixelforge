<?php

declare(strict_types=1);

namespace App\Http\Integrations\Pixelforge\Requests;

use App\Data\GenerationData;
use Override;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Http\Response;

final class GetGenerationRequest extends Request
{
    protected Method $method = Method::GET;

    public function __construct(
        private readonly string $pixelforgeId,
    ) {
        //
    }

    public function resolveEndpoint(): string
    {
        return '/generations/'.rawurlencode($this->pixelforgeId);
    }

    #[Override]
    public function createDtoFromResponse(Response $response): GenerationData
    {
        return GenerationData::fromArray($response->array());
    }
}
