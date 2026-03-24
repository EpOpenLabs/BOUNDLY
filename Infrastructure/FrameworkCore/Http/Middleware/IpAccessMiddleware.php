<?php

declare(strict_types=1);

namespace Infrastructure\FrameworkCore\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Infrastructure\FrameworkCore\Enums\ErrorCode;
use Infrastructure\FrameworkCore\Services\Security\IpAccessControl;
use Infrastructure\FrameworkCore\Traits\ApiResponse;
use Symfony\Component\HttpFoundation\Response;

class IpAccessMiddleware
{
    use ApiResponse;

    public function __construct(
        protected IpAccessControl $accessControl
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        if (! $this->accessControl->isEnabled()) {
            return $next($request);
        }

        $clientIp = $request->ip();

        if (! $this->accessControl->isAllowed($clientIp)) {
            return $this->error(
                'Access denied from your IP address',
                ErrorCode::IP_RESTRICTED,
                403
            );
        }

        return $next($request);
    }
}
