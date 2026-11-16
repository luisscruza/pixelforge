<?php

declare(strict_types=1);

namespace App\Http\Integrations\Pixelforge;

use Illuminate\Support\Fluent;
use Saloon\Exceptions\Request\RequestException;
use Saloon\Http\Response;
use Throwable;

final class PixelforgeException extends RequestException
{
    private const array RETRYABLE_CODES = [
        'rate_limit_exceeded',
        'concurrency_limit_exceeded',
        'overloaded',
    ];

    public function __construct(
        Response $response,
        public readonly string $errorCode,
        public readonly ?int $retryAfterSeconds,
        string $message,
        ?Throwable $previous = null,
    ) {
        parent::__construct($response, $message, $response->status(), $previous);
    }

    public static function fromResponse(Response $response, ?Throwable $previous = null): self
    {
        $body = json_decode($response->body(), true);
        $error = new Fluent(is_array($body) && is_array($body['error'] ?? null) ? $body['error'] : []);

        $code = $error->get('code');
        $message = $error->get('message');
        $retryAfter = $response->header('Retry-After');

        return new self(
            response: $response,
            errorCode: is_string($code) ? $code : 'http_'.$response->status(),
            retryAfterSeconds: is_string($retryAfter) && ctype_digit($retryAfter) ? (int) $retryAfter : null,
            message: is_string($message) ? $message : 'Pixelforge returned HTTP '.$response->status().'.',
            previous: $previous,
        );
    }

    public function leavesOutcomeUnknown(): bool
    {
        return $this->getStatus() >= 500 && str_starts_with($this->errorCode, 'http_');
    }

    public function isRetryable(): bool
    {
        return in_array($this->errorCode, self::RETRYABLE_CODES, true) || $this->getStatus() >= 500;
    }
}
