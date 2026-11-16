<?php

declare(strict_types=1);

namespace App\Http\Integrations\Pixelforge;

use App\Data\GenerationData;
use App\Data\GenerationInput;
use App\Data\PricingData;
use App\Data\UsageDay;
use App\Http\Integrations\Pixelforge\Requests\CreateGenerationRequest;
use App\Http\Integrations\Pixelforge\Requests\GetGenerationRequest;
use App\Http\Integrations\Pixelforge\Requests\GetPricingRequest;
use App\Http\Integrations\Pixelforge\Requests\GetUsageRequest;
use Carbon\CarbonImmutable;
use Illuminate\Container\Attributes\Config;
use Override;
use Saloon\Contracts\Authenticator;
use Saloon\Http\Auth\TokenAuthenticator;
use Saloon\Http\Connector;
use Saloon\Http\Response;
use Saloon\Traits\Plugins\AcceptsJson;
use Saloon\Traits\Plugins\AlwaysThrowOnErrors;
use Saloon\Traits\Plugins\HasTimeout;
use Throwable;

final class PixelforgeConnector extends Connector
{
    use AcceptsJson;
    use AlwaysThrowOnErrors;
    use HasTimeout;

    protected int $connectTimeout = 5;

    protected int $requestTimeout = 15;

    public function __construct(
        #[Config('services.pixelforge.base_url')]
        private readonly string $baseUrl,
        #[Config('services.pixelforge.api_key')]
        private readonly ?string $apiKey,
    ) {
        //
    }

    public function resolveBaseUrl(): string
    {
        return $this->baseUrl;
    }

    #[Override]
    public function getRequestException(Response $response, ?Throwable $senderException): PixelforgeException
    {
        return PixelforgeException::fromResponse($response, $senderException);
    }

    /**
     * @param  array<string, string>  $metadata
     */
    public function createGeneration(GenerationInput $input, string $idempotencyKey, array $metadata = []): GenerationData
    {
        $request = new CreateGenerationRequest($input, $idempotencyKey, $metadata);

        return $request->createDtoFromResponse($this->send($request));
    }

    public function generation(string $pixelforgeId): GenerationData
    {
        $request = new GetGenerationRequest($pixelforgeId);

        return $request->createDtoFromResponse($this->send($request));
    }

    public function pricing(): PricingData
    {
        $request = new GetPricingRequest;

        return $request->createDtoFromResponse($this->send($request));
    }

    /**
     * @return list<UsageDay>
     */
    public function usage(CarbonImmutable $startDate, CarbonImmutable $endDate): array
    {
        $request = new GetUsageRequest($startDate, $endDate);

        return $request->createDtoFromResponse($this->send($request));
    }

    protected function defaultAuth(): Authenticator
    {
        return new TokenAuthenticator((string) $this->apiKey);
    }
}
