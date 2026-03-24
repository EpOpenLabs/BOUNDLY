<?php

namespace Infrastructure\FrameworkCore\Contracts\Authentication;

use Illuminate\Contracts\Auth\Authenticatable;

interface ApiKeyProviderInterface
{
    /**
     * Validate an API key.
     */
    public function validateKey(string $key): bool;

    /**
     * Get the user associated with an API key.
     */
    public function getUserFromKey(string $key): ?Authenticatable;

    /**
     * Create a new API key for a user.
     *
     * @param  array<string>  $scopes
     * @return string The plain text API key (shown only once)
     */
    public function createKey(Authenticatable $user, array $scopes = [], ?string $name = null): string;

    /**
     * Revoke an API key.
     */
    public function revokeKey(string $key): bool;

    /**
     * Revoke all API keys for a user.
     *
     * @return int Number of keys revoked
     */
    public function revokeAllKeys(Authenticatable $user): int;

    /**
     * Get the scopes/permissions associated with an API key.
     *
     * @return array<string>
     */
    public function getKeyScopes(string $key): array;

    /**
     * Check if a key has a specific scope.
     */
    public function keyHasScope(string $key, string $scope): bool;
}
