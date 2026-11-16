<?php

declare(strict_types=1);

namespace App\Http\Integrations\Pixelforge\Requests;

use App\Data\GenerationData;
use App\Data\GenerationInput;
use Override;
use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Http\Response;
use Saloon\Traits\Body\HasJsonBody;

final class CreateGenerationRequest extends Request implements HasBody
{
    use HasJsonBody;

    protected Method $method = Method::POST;

    /**
     * @param  array<string, string>  $metadata
     */
    public function __construct(
        private readonly GenerationInput $input,
        private readonly string $idempotencyKey,
        private readonly array $metadata = [],
    ) {
        //
    }

    public function resolveEndpoint(): string
    {
        return '/generations';
    }

    #[Override]
    public function createDtoFromResponse(Response $response): GenerationData
    {
        return GenerationData::fromArray($response->array());
    }

    /**
     * @return array<string, string>
     */
    protected function defaultHeaders(): array
    {
        return ['Idempotency-Key' => $this->idempotencyKey];
    }

    /**
     * @return array{prompt: string, size: string, quality: string, reference_images: list<string>, metadata: array<string, string>|object}
     */
    protected function defaultBody(): array
    {
        return [
            'prompt' => $this->input->prompt,
            'size' => $this->input->size->value,
            'quality' => $this->input->quality->value,
            'reference_images' => $this->input->referenceImages,
            'metadata' => $this->metadata === [] ? (object) [] : $this->metadata,
        ];
    }
}
