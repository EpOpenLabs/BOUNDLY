<?php

namespace Infrastructure\FrameworkCore\Contracts\Authentication;

use Illuminate\Contracts\Auth\Authenticatable;

interface TokenValidatorInterface
{
    /**
     * Validate a token and return the associated user.
     */
    public function validateToken(string $token): ?Authenticatable;

    /**
     * Check if a token is currently active.
     */
    public function isTokenActive(string $token): bool;

    /**
     * Get the time-to-live (in seconds) remaining for a token.
     *
     * @return int|null Null if token has no expiration
     */
    public function getTokenTTL(string $token): ?int;
}
