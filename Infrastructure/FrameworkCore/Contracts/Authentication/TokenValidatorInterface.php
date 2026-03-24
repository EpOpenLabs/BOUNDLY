<?php

namespace Infrastructure\FrameworkCore\Contracts\Authentication;

use Illuminate\Contracts\Auth\Authenticatable;

interface TokenValidatorInterface
{
    /**
     * Validate a token and return the associated user.
     *
     * @param  string  $token
     * @return Authenticatable|null
     */
    public function validateToken(string $token): ?Authenticatable;

    /**
     * Check if a token is currently active.
     *
     * @param  string  $token
     * @return bool
     */
    public function isTokenActive(string $token): bool;

    /**
     * Get the time-to-live (in seconds) remaining for a token.
     *
     * @param  string  $token
     * @return int|null  Null if token has no expiration
     */
    public function getTokenTTL(string $token): ?int;
}
