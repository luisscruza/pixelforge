<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Container\Attributes\Config;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final readonly class VerifyPixelforgeSignature
{
    public function __construct(
        #[Config('services.pixelforge.webhook_secret')]
        private ?string $secret,
        #[Config('services.pixelforge.webhook_tolerance_seconds')]
        private int $toleranceSeconds,
    ) {
        //
    }

    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        abort_unless($this->isValid($request), Response::HTTP_BAD_REQUEST, 'Invalid Pixelforge signature.');

        return $next($request);
    }

    private function isValid(Request $request): bool
    {
        if ($this->secret === null || $this->secret === '') {
            return false;
        }

        $signature = $this->parse($request->header('Pixelforge-Signature', ''));
        $timestamp = $signature['t'] ?? '';
        $provided = $signature['v1'] ?? '';

        if (! ctype_digit($timestamp) || $provided === '') {
            return false;
        }

        if (abs(now()->getTimestamp() - (int) $timestamp) > $this->toleranceSeconds) {
            return false;
        }

        $expected = hash_hmac('sha256', $timestamp.'.'.$request->getContent(), $this->secret);

        return hash_equals($expected, $provided);
    }

    /**
     * @return array<string, string>
     */
    private function parse(string $header): array
    {
        $pairs = [];

        foreach (explode(',', $header) as $part) {
            [$key, $value] = array_pad(explode('=', mb_trim($part), 2), 2, '');
            $pairs[$key] = $value;
        }

        return $pairs;
    }
}
