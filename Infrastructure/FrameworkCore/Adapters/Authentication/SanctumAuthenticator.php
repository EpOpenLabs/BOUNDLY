<?php

namespace Infrastructure\FrameworkCore\Adapters\Authentication;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Infrastructure\FrameworkCore\Contracts\Authentication\TokenValidatorInterface;
use Infrastructure\FrameworkCore\Contracts\Authentication\UserResolverInterface;

class SanctumAuthenticator extends AbstractAuthenticator
{
    protected function getGuardName(): string
    {
        return config('boundly.auth.default_guard', 'sanctum');
    }

    public function authenticate(array $credentials): ?Authenticatable
    {
        if (empty($credentials)) {
            return null;
        }

        $guard = $this->getGuardName();

        $authGuard = Auth::guard($guard);

        if (method_exists($authGuard, 'attempt') && $authGuard->attempt($credentials)) {
            return $authGuard->user();
        }

        $user = $authGuard->user();
        if ($user && $this->validateCredentials($user, $credentials)) {
            return $user;
        }

        return null;
    }

    public function validateCredentials(Authenticatable $user, array $credentials): bool
    {
        $guard = $this->getGuardName();

        return Auth::guard($guard)->validate($credentials);
    }
}

class SanctumTokenValidator implements TokenValidatorInterface
{
    public function validateToken(string $token): ?Authenticatable
    {
        $guard = config('boundly.auth.default_guard', 'sanctum');

        $user = Auth::guard($guard)->user();

        if ($user) {
            return $user;
        }

        return null;
    }

    public function isTokenActive(string $token): bool
    {
        $guard = config('boundly.auth.default_guard', 'sanctum');

        return Auth::guard($guard)->check();
    }

    public function getTokenTTL(string $token): ?int
    {
        return null;
    }
}

class SanctumUserResolver implements UserResolverInterface
{
    public function resolveFromRequest(Request $request): ?Authenticatable
    {
        $guard = config('boundly.auth.default_guard', 'sanctum');

        return Auth::guard($guard)->user();
    }

    public function hasAuthenticatedUser(Request $request): bool
    {
        $guard = config('boundly.auth.default_guard', 'sanctum');

        return Auth::guard($guard)->check();
    }
}
