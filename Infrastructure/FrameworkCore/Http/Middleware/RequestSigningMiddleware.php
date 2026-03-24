<?php

declare(strict_types=1);

namespace Infrastructure\FrameworkCore\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Infrastructure\FrameworkCore\Enums\ErrorCode;
use Infrastructure\FrameworkCore\Services\Security\RequestSigningService;
use Infrastructure\FrameworkCore\Traits\ApiResponse;
use Symfony\Component\HttpFoundation\Response;

class RequestSigningMiddleware
{
    use ApiResponse;

    public function __construct(
        protected RequestSigningService $signingService
    ) {}

    public function handle(Request $request, Closure $next, bool $required = true): Response
    {
        if (! $this->signingService->isEnabled()) {
            return $next($request);
        }

        if (! $this->signingService->verifySignature($request)) {
            if ($required) {
                return $this->error(
                    'Invalid request signature',
                    ErrorCode::SIGNATURE_INVALID,
                    401
                );
            }

            return $this->error(
                'Missing request signature',
                ErrorCode::SIGNATURE_INVALID,
                401
            );
        }

        return $next($request);
    }
}
