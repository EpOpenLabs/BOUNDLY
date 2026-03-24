<?php

namespace Infrastructure\FrameworkCore\Contracts\Authentication;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Http\Request;

interface UserResolverInterface
{
    /**
     * Resolve the authenticated user from the current request.
     *
     * @param  Request  $request
     * @return Authenticatable|null
     */
    public function resolveFromRequest(Request $request): ?Authenticatable;

    /**
     * Check if the current request has an authenticated user.
     *
     * @param  Request  $request
     * @return bool
     */
    public function hasAuthenticatedUser(Request $request): bool;
}
