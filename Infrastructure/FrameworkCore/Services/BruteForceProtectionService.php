<?php

declare(strict_types=1);

namespace Infrastructure\FrameworkCore\Services;

use Illuminate\Cache\RateLimiter;
use Illuminate\Http\Request;

class BruteForceProtectionService
{
    protected array $config;
    protected RateLimiter $limiter;
    protected SecurityLogger $logger;

    public function __construct(RateLimiter $limiter, SecurityLogger $logger, ?array $config = null)
    {
        $this->limiter = $limiter;
        $this->logger = $logger;
        $this->config = $config ?? $this->getConfig();
    }

    protected function getConfig(): array
    {
        if (function_exists('config') && app()->bound('config')) {
            return config('boundly.brute_force', []);
        }

        return [];
    }

    public function isEnabled(): bool
    {
        return $this->config['enabled'] ?? true;
    }

    public function getMaxAttempts(): int
    {
        return $this->config['max_attempts'] ?? 5;
    }

    public function getDecayMinutes(): int
    {
        return $this->config['decay_minutes'] ?? 15;
    }

    public function getLockoutMultiplier(): int
    {
        return $this->config['lockout_multiplier'] ?? 2;
    }

    public function getMaxLockouts(): int
    {
        return $this->config['max_lockouts'] ?? 3;
    }

    public function getTrackBy(): string
    {
        return $this->config['track_by'] ?? 'email';
    }

    public function getIdentifier(Request $request, ?array $credentials = null): string
    {
        $trackBy = $this->getTrackBy();

        if ($credentials && isset($credentials[$trackBy])) {
            return $credentials[$trackBy];
        }

        if ($request->hasHeader('X-Forwarded-For')) {
            return $request->header('X-Forwarded-For');
        }

        return $request->ip() ?? 'unknown';
    }

    public function tooManyAttempts(string $identifier): bool
    {
        $key = $this->getKey($identifier);

        return $this->limiter->tooManyAttempts($key, $this->getMaxAttempts());
    }

    public function hits(string $identifier, ?int $decaySeconds = null): void
    {
        $key = $this->getKey($identifier);
        $decay = $decaySeconds ?? ($this->getDecayMinutes() * 60);

        $this->limiter->hit($key, $decay);
    }

    public function attempts(string $identifier): int
    {
        $key = $this->getKey($identifier);

        return $this->limiter->attempts($key);
    }

    public function availableIn(string $identifier): int
    {
        $key = $this->getKey($identifier);

        return $this->limiter->availableIn($key);
    }

    public function clear(string $identifier): void
    {
        $key = $this->getKey($identifier);
        $this->limiter->resetAttempts($key);
    }

    public function isLockedOut(string $identifier): bool
    {
        $lockoutKey = $this->getLockoutKey($identifier);

        return $this->limiter->tooManyAttempts($lockoutKey, $this->getMaxLockouts());
    }

    public function lockout(string $identifier): void
    {
        $lockoutKey = $this->getLockoutKey($identifier);
        $multiplier = $this->getLockoutMultiplier();
        $decayMinutes = $this->getDecayMinutes();

        $this->limiter->hit($lockoutKey, $decayMinutes * 60 * $multiplier);
        $this->logger->logBruteForceBlocked($identifier, null, [
            'lockout_duration' => $decayMinutes * $multiplier,
        ]);
    }

    public function clearLockout(string $identifier): void
    {
        $lockoutKey = $this->getLockoutKey($identifier);
        $this->limiter->resetAttempts($lockoutKey);
    }

    public function getLockoutKey(string $identifier): string
    {
        return 'brute_force_lockout:'.$identifier;
    }

    public function getKey(string $identifier): string
    {
        return 'brute_force:'.$identifier;
    }

    public function recordFailedAttempt(string $identifier, ?Request $request = null): void
    {
        $attempts = $this->attempts($identifier) + 1;

        $this->hits($identifier);

        if ($this->tooManyAttempts($identifier)) {
            $this->logger->logBruteForceDetected($identifier, $request, [
                'attempts' => $attempts,
                'max_attempts' => $this->getMaxAttempts(),
            ]);
        }
    }

    public function recordSuccessfulAttempt(string $identifier): void
    {
        $this->clear($identifier);
        $this->clearLockout($identifier);
    }

    public function getRetryAfter(string $identifier): int
    {
        return $this->availableIn($identifier);
    }

    public function remainingAttempts(string $identifier): int
    {
        return max(0, $this->getMaxAttempts() - $this->attempts($identifier));
    }
}
