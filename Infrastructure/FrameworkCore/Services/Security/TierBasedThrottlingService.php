<?php

declare(strict_types=1);

namespace Infrastructure\FrameworkCore\Services\Security;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class TierBasedThrottlingService
{
    protected array $tiers;
    protected string $cacheStore;

    public function __construct()
    {
        $config = config('boundly.security.tier_throttling', []);
        $this->tiers = $config['tiers'] ?? $this->getDefaultTiers();
        $this->cacheStore = $config['cache_store'] ?? 'file';
    }

    protected function getDefaultTiers(): array
    {
        return [
            'free' => [
                'requests_per_minute' => 60,
                'requests_per_hour' => 1000,
                'requests_per_day' => 10000,
            ],
            'basic' => [
                'requests_per_minute' => 300,
                'requests_per_hour' => 5000,
                'requests_per_day' => 50000,
            ],
            'pro' => [
                'requests_per_minute' => 1000,
                'requests_per_hour' => 20000,
                'requests_per_day' => 200000,
            ],
            'enterprise' => [
                'requests_per_minute' => 5000,
                'requests_per_hour' => 100000,
                'requests_per_day' => 1000000,
            ],
        ];
    }

    public function getTiers(): array
    {
        return $this->tiers;
    }

    public function getTierLimits(string $tier): array
    {
        return $this->tiers[$tier] ?? $this->tiers['free'];
    }

    public function checkLimit(Request $request, string $tier = 'free'): array
    {
        $tierLimits = $this->getTierLimits($tier);
        $identifier = $this->getIdentifier($request);

        $minuteResult = $this->checkPeriodLimit($identifier, 'minute', $tierLimits['requests_per_minute']);
        if (! $minuteResult['allowed']) {
            return $minuteResult;
        }

        $hourResult = $this->checkPeriodLimit($identifier, 'hour', $tierLimits['requests_per_hour']);
        if (! $hourResult['allowed']) {
            return $hourResult;
        }

        $dayResult = $this->checkPeriodLimit($identifier, 'day', $tierLimits['requests_per_day']);
        if (! $dayResult['allowed']) {
            return $dayResult;
        }

        return [
            'allowed' => true,
            'tier' => $tier,
            'limit_type' => 'unlimited',
        ];
    }

    protected function checkPeriodLimit(string $identifier, string $period, int $limit): array
    {
        $key = "throttle:{$identifier}:{$period}";
        $window = match ($period) {
            'minute' => 60,
            'hour' => 3600,
            'day' => 86400,
            default => 60,
        };

        $count = (int) Cache::store($this->cacheStore)->get($key, 0);

        if ($count >= $limit) {
            return [
                'allowed' => false,
                'tier' => 'unknown',
                'limit_type' => $period,
                'limit' => $limit,
                'remaining' => 0,
                'retry_after' => 60,
            ];
        }

        if ($count === 0) {
            Cache::store($this->cacheStore)->put($key, 1, $window);
        } else {
            Cache::store($this->cacheStore)->increment($key);
        }

        return [
            'allowed' => true,
            'tier' => 'unknown',
            'limit_type' => $period,
            'limit' => $limit,
            'remaining' => $limit - $count - 1,
        ];
    }

    protected function getIdentifier(Request $request): string
    {
        $apiKey = $request->header('X-API-Key');
        if ($apiKey) {
            return 'api_key:' . substr($apiKey, 0, 8);
        }

        $userId = $request->header('X-User-ID');
        if ($userId) {
            return 'user:' . $userId;
        }

        return 'ip:' . $request->ip();
    }

    public function resetLimit(Request $request): void
    {
        $identifier = $this->getIdentifier($request);

        Cache::store($this->cacheStore)->forget("throttle:{$identifier}:minute");
        Cache::store($this->cacheStore)->forget("throttle:{$identifier}:hour");
        Cache::store($this->cacheStore)->forget("throttle:{$identifier}:day");
    }

    public function getUsage(Request $request): array
    {
        $identifier = $this->getIdentifier($request);

        return [
            'minute' => (int) Cache::store($this->cacheStore)->get("throttle:{$identifier}:minute", 0),
            'hour' => (int) Cache::store($this->cacheStore)->get("throttle:{$identifier}:hour", 0),
            'day' => (int) Cache::store($this->cacheStore)->get("throttle:{$identifier}:day", 0),
        ];
    }
}
