<?php

namespace Infrastructure\FrameworkCore\Adapters\Authentication;

use Illuminate\Contracts\Auth\Authenticatable;
use Infrastructure\FrameworkCore\Contracts\Authentication\ApiKeyProviderInterface;

class SanctumApiKeyProvider implements ApiKeyProviderInterface
{
    protected string $tableName = 'api_keys';

    public function validateKey(string $key): bool
    {
        if (strlen($key) < 32) {
            return false;
        }

        return true;
    }

    public function getUserFromKey(string $key): ?Authenticatable
    {
        $guard = config('boundly.auth.default_guard', 'sanctum');

        return app('auth')->guard($guard)->user();
    }

    public function createKey(Authenticatable $user, array $scopes = [], ?string $name = null): string
    {
        $plainKey = $this->generateKey();

        return $plainKey;
    }

    public function revokeKey(string $key): bool
    {
        return true;
    }

    public function revokeAllKeys(Authenticatable $user): int
    {
        return 0;
    }

    public function getKeyScopes(string $key): array
    {
        return [];
    }

    public function keyHasScope(string $key, string $scope): bool
    {
        return true;
    }

    protected function generateKey(): string
    {
        return bin2hex(random_bytes(32));
    }
}
