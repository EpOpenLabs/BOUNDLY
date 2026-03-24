<?php

namespace Infrastructure\FrameworkCore\Http\Middleware;

use Closure;
use Illuminate\Cache\RateLimiter;
use Illuminate\Http\Request;
use Infrastructure\FrameworkCore\Attributes\Behavior\RateLimit;
use Infrastructure\FrameworkCore\Registry\EntityRegistry;
use Symfony\Component\HttpFoundation\Response;

class RateLimitMiddleware
{
    public function __construct(
        protected RateLimiter $limiter
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        $config = app(EntityRegistry::class)
            ->getEntityConfig($request->route('resource'));

        if (! $config) {
            return $next($request);
        }

        $rateLimitConfig = config('boundly.rate_limit', []);

        if (! ($rateLimitConfig['enabled'] ?? true)) {
            return $next($request);
        }

        $className = $config['class'];

        $maxAttempts = $rateLimitConfig['max_attempts'] ?? 60;
        $decayMinutes = $rateLimitConfig['decay_minutes'] ?? 1;
        $prefix = $rateLimitConfig['prefix'] ?? 'api';

        $reflection = new \ReflectionClass($className);
        $rateLimitAttr = $reflection->getAttributes(RateLimit::class)[0] ?? null;

        if ($rateLimitAttr) {
            $instance = $rateLimitAttr->newInstance();
            $maxAttempts = $instance->maxAttempts;
            $decayMinutes = $instance->decayMinutes;
            $prefix = $instance->prefix ?? "api:{$config['resource']}";
        }

        $key = $this->resolveRequestSignature($request, $prefix);

        if ($this->limiter->tooManyAttempts($key, $maxAttempts)) {
            return $this->buildTooManyAttemptsResponse($key, $maxAttempts, $decayMinutes);
        }

        $this->limiter->hit($key, $decayMinutes * 60);

        $response = $next($request);

        return $this->addRateLimitHeaders(
            $response,
            $maxAttempts,
            $this->calculateRemainingAttempts($key, $maxAttempts),
            $decayMinutes
        );
    }

    protected function resolveRequestSignature(Request $request, string $prefix): string
    {
        $resource = $request->route('resource') ?? 'global';
        $id = $request->route('id');

        if ($id) {
            return $prefix.'|'.$resource.'|'.$id.'|'.$request->ip();
        }

        $user = $request->user();
        if ($user) {
            return $prefix.'|'.$resource.'|'.$user->getAuthIdentifier();
        }

        return $prefix.'|'.$resource.'|'.$request->ip();
    }

    protected function tooManyAttempts(string $key, int $maxAttempts): bool
    {
        return $this->limiter->tooManyAttempts($key, $maxAttempts);
    }

    protected function hit(string $key, int $decaySeconds): void
    {
        $this->limiter->hit($key, $decaySeconds);
    }

    protected function calculateRemainingAttempts(string $key, int $maxAttempts): int
    {
        return $maxAttempts - $this->limiter->attempts($key);
    }

    protected function buildTooManyAttemptsResponse(string $key, int $maxAttempts, int $decayMinutes): Response
    {
        $retryAfter = $this->limiter->availableIn($key);

        return response()->json([
            'status' => 'error',
            'message' => __('core::messages.rate_limit_exceeded'),
            'error' => [
                'max_attempts' => $maxAttempts,
                'retry_after' => $retryAfter,
            ],
        ], 429, [
            'Retry-After' => $retryAfter,
            'X-RateLimit-Limit' => $maxAttempts,
            'X-RateLimit-Remaining' => 0,
        ]);
    }

    protected function addRateLimitHeaders(Response $response, int $maxAttempts, int $remaining, int $decayMinutes): Response
    {
        $response->headers->add([
            'X-RateLimit-Limit' => $maxAttempts,
            'X-RateLimit-Remaining' => max(0, $remaining),
        ]);

        return $response;
    }
}
