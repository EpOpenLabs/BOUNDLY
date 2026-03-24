<?php

namespace Infrastructure\FrameworkCore\Traits;

use Illuminate\Contracts\Auth\Authenticatable;
use Infrastructure\FrameworkCore\Contracts\Authentication\AuthenticatorInterface;
use Infrastructure\FrameworkCore\Contracts\Authentication\TokenValidatorInterface;
use Infrastructure\FrameworkCore\Contracts\Authentication\UserResolverInterface;

trait ResolvesAuthentication
{
    protected function getAuthenticator(): ?AuthenticatorInterface
    {
        $class = config('boundly.auth.authenticator');

        if (! $class || ! class_exists($class)) {
            return null;
        }

        return app($class);
    }

    protected function getTokenValidator(): ?TokenValidatorInterface
    {
        $class = config('boundly.auth.token_validator');

        if (! $class || ! class_exists($class)) {
            return null;
        }

        return app($class);
    }

    protected function getUserResolver(): ?UserResolverInterface
    {
        $class = config('boundly.auth.user_resolver');

        if (! $class || ! class_exists($class)) {
            return null;
        }

        return app($class);
    }

    protected function resolveUser(): ?Authenticatable
    {
        $resolver = $this->getUserResolver();

        if ($resolver) {
            return $resolver->resolveFromRequest(request());
        }

        $guard = config('boundly.auth.default_guard', 'sanctum');

        return app('auth')->guard($guard)->user();
    }
}
