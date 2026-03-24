<?php

declare(strict_types=1);

namespace Infrastructure\FrameworkCore\Services\Security;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class IpAccessControl
{
    protected bool $enabled;
    protected array $whitelist;
    protected array $blacklist;
    protected string $defaultAction;
    protected string $cacheStore;

    public function __construct()
    {
        $config = config('boundly.security.ip_access', []);
        $this->enabled = $config['enabled'] ?? false;
        $this->whitelist = $config['whitelist'] ?? [];
        $this->blacklist = $config['blacklist'] ?? [];
        $this->defaultAction = $config['default_action'] ?? 'deny';
        $this->cacheStore = $config['cache_store'] ?? 'file';
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    public function isAllowed(string $ip): bool
    {
        if (! $this->enabled) {
            return true;
        }

        if ($this->isBlacklisted($ip)) {
            return false;
        }

        if (! empty($this->whitelist)) {
            return $this->isWhitelisted($ip);
        }

        return $this->defaultAction === 'allow';
    }

    public function isBlacklisted(string $ip): bool
    {
        foreach ($this->blacklist as $pattern) {
            if ($this->ipMatchesPattern($ip, $pattern)) {
                return true;
            }
        }

        return false;
    }

    public function isWhitelisted(string $ip): bool
    {
        foreach ($this->whitelist as $pattern) {
            if ($this->ipMatchesPattern($ip, $pattern)) {
                return true;
            }
        }

        return false;
    }

    public function addToBlacklist(string $ip): void
    {
        if (! in_array($ip, $this->blacklist)) {
            $this->blacklist[] = $ip;
            $this->persistBlacklist();
        }
    }

    public function removeFromBlacklist(string $ip): void
    {
        $this->blacklist = array_filter($this->blacklist, fn ($p) => $p !== $ip);
        $this->persistBlacklist();
    }

    public function addToWhitelist(string $ip): void
    {
        if (! in_array($ip, $this->whitelist)) {
            $this->whitelist[] = $ip;
            $this->persistWhitelist();
        }
    }

    public function removeFromWhitelist(string $ip): void
    {
        $this->whitelist = array_filter($this->whitelist, fn ($p) => $p !== $ip);
        $this->persistWhitelist();
    }

    public function checkRequest(Request $request): bool
    {
        return $this->isAllowed($request->ip());
    }

    public function getWhitelist(): array
    {
        return $this->whitelist;
    }

    public function getBlacklist(): array
    {
        return $this->blacklist;
    }

    protected function ipMatchesPattern(string $ip, string $pattern): bool
    {
        if ($pattern === $ip) {
            return true;
        }

        if (str_contains($pattern, '/')) {
            return $this->ipInCidr($ip, $pattern);
        }

        if (str_contains($pattern, '*')) {
            return $this->ipMatchesWildcard($ip, $pattern);
        }

        return false;
    }

    protected function ipInCidr(string $ip, string $cidr): bool
    {
        if (! str_contains($cidr, '/')) {
            return $ip === $cidr;
        }

        [$subnet, $mask] = explode('/', $cidr);

        $maskBits = (int) $mask;
        $ipLong = ip2long($ip);
        $subnetLong = ip2long($subnet);

        if ($ipLong === false || $subnetLong === false) {
            return false;
        }

        $maskLong = -1 << (32 - $maskBits);

        return ($ipLong & $maskLong) === ($subnetLong & $maskLong);
    }

    protected function ipMatchesWildcard(string $ip, string $pattern): bool
    {
        $regex = '/^' . str_replace(['.', '*'], ['\\.', '.*'], $pattern) . '$/';

        return (bool) preg_match($regex, $ip);
    }

    protected function persistBlacklist(): void
    {
        Cache::store($this->cacheStore)->put('boundly:ip_blacklist', $this->blacklist, 0);
    }

    protected function persistWhitelist(): void
    {
        Cache::store($this->cacheStore)->put('boundly:ip_whitelist', $this->whitelist, 0);
    }
}
