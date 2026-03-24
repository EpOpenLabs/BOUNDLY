<?php

declare(strict_types=1);

namespace Infrastructure\FrameworkCore\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Infrastructure\FrameworkCore\Enums\ErrorCode;
use Infrastructure\FrameworkCore\Services\Security\TierBasedThrottlingService;
use Infrastructure\FrameworkCore\Traits\ApiResponse;
use Symfony\Component\HttpFoundation\Response;

class TierThrottleMiddleware
{
    use ApiResponse;

    public function __construct(
        protected TierBasedThrottlingService $throttlingService
    ) {}

    public function handle(Request $request, Closure $next, string $tier = 'free'): Response
    {
        $result = $this->throttlingService->checkLimit($request, $tier);

        $response = $next($request);

        if ($result['allowed']) {
            $response->headers->set('X-RateLimit-Limit', (string) $result['limit']);
            $response->headers->set('X-RateLimit-Remaining', (string) $result['remaining']);
        } else {
            return $this->error(
                "Rate limit exceeded for {$result['limit_type']}",
                ErrorCode::TIER_LIMIT_EXCEEDED,
                429,
                [
                    'limit' => $result['limit'],
                    'limit_type' => $result['limit_type'],
                    'retry_after' => $result['retry_after'] ?? 60,
                ]
            );
        }

        return $response;
    }
}
