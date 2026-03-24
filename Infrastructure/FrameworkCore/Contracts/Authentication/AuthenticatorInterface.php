<?php

namespace Infrastructure\FrameworkCore\Contracts\Authentication;

use Illuminate\Contracts\Auth\Authenticatable;

interface AuthenticatorInterface
{
    /**
     * Authenticate a user with credentials.
     *
     * @param  array<string, mixed>  $credentials
     */
    public function authenticate(array $credentials): ?Authenticatable;

    /**
     * Validate a user's credentials.
     *
     * @param  array<string, mixed>  $credentials
     */
    public function validateCredentials(Authenticatable $user, array $credentials): bool;
}
