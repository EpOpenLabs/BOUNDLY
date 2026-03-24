<?php

namespace Infrastructure\FrameworkCore\Contracts\Authentication;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Http\Request;

interface UserResolverInterface
{
    /**
     * Resolve the authenticated user from the current request.
     */
    public function resolveFromRequest(Request $request): ?Authenticatable;

    /**
     * Check if the current request has an authenticated user.
     */
    public function hasAuthenticatedUser(Request $request): bool;
}
