<?php

declare(strict_types=1);

namespace Infrastructure\FrameworkCore\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Infrastructure\FrameworkCore\Services\BruteForceProtectionService;
use Symfony\Component\HttpFoundation\Response;

class BruteForceProtectionMiddleware
{
    public function __construct(
        protected BruteForceProtectionService $bruteForce
    ) {}

    public function handle(Request $request, Closure $next, ?string $identifier = null): Response
    {
        if (! $this->bruteForce->isEnabled()) {
            return $next($request);
        }

        $identifier = $identifier ?? $this->bruteForce->getIdentifier($request);

        if ($this->bruteForce->isLockedOut($identifier)) {
            return $this->buildLockedOutResponse($identifier);
        }

        if ($this->bruteForce->tooManyAttempts($identifier)) {
            $this->bruteForce->lockout($identifier);

            return $this->buildLockedOutResponse($identifier);
        }

        $response = $next($request);

        if ($this->isFailedResponse($response)) {
            $this->bruteForce->recordFailedAttempt($identifier, $request);
        } elseif ($this->isSuccessfulResponse($response)) {
            $this->bruteForce->recordSuccessfulAttempt($identifier);
        }

        return $response;
    }

    protected function isFailedResponse(Response $response): bool
    {
        $statusCode = $response->getStatusCode();

        return in_array($statusCode, [401, 403, 422], true);
    }

    protected function isSuccessfulResponse(Response $response): bool
    {
        $statusCode = $response->getStatusCode();

        return $statusCode >= 200 && $statusCode < 300;
    }

    protected function buildLockedOutResponse(string $identifier): Response
    {
        $retryAfter = $this->bruteForce->getRetryAfter($identifier);

        return response()->json([
            'status' => 'error',
            'message' => __('core::messages.too_many_attempts'),
            'error' => [
                'code' => 'BRUTE_FORCE_LOCKOUT',
                'retry_after' => $retryAfter,
                'locked_until' => now()->addSeconds($retryAfter)->toIso8601String(),
            ],
        ], 429, [
            'Retry-After' => $retryAfter,
            'X-RateLimit-Remaining' => 0,
        ]);
    }

    public function getService(): BruteForceProtectionService
    {
        return $this->bruteForce;
    }
}
