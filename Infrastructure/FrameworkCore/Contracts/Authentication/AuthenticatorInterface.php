<?php

namespace Infrastructure\FrameworkCore\Contracts\Authentication;

use Illuminate\Contracts\Auth\Authenticatable;

interface AuthenticatorInterface
{
    /**
     * Authenticate a user with credentials.
     *
     * @param  array<string, mixed>  $credentials
     * @return Authenticatable|null
     */
    public function authenticate(array $credentials): ?Authenticatable;

    /**
     * Validate a user's credentials.
     *
     * @param  Authenticatable  $user
     * @param  array<string, mixed>  $credentials
     * @return bool
     */
    public function validateCredentials(Authenticatable $user, array $credentials): bool;
}
