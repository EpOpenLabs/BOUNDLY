<?php

declare(strict_types=1);

namespace Infrastructure\FrameworkCore\Services\Health\Checks;

use Illuminate\Support\Facades\Cache;
use Infrastructure\FrameworkCore\Contracts\Health\HealthCheckInterface;
use Infrastructure\FrameworkCore\Contracts\Health\HealthCheckResult;

class CacheHealthCheck implements HealthCheckInterface
{
    public function name(): string
    {
        return 'cache';
    }

    public function severity(): string
    {
        return 'critical';
    }

    public function check(): HealthCheckResult
    {
        try {
            $start = microtime(true);
            $testKey = 'health_check_'.uniqid();
            $testValue = 'test_'.time();

            Cache::put($testKey, $testValue, 10);
            $retrieved = Cache::get($testKey);
            Cache::forget($testKey);

            $latency = round((microtime(true) - $start) * 1000, 2);

            if ($retrieved !== $testValue) {
                return HealthCheckResult::unhealthy('Cache read/write verification failed');
            }

            return HealthCheckResult::healthy('Cache is healthy', [
                'latency_ms' => $latency,
                'driver' => $this->getDriverName(),
            ]);
        } catch (\Exception $e) {
            return HealthCheckResult::unhealthy('Cache check failed: '.$e->getMessage());
        }
    }

    protected function getDriverName(): string
    {
        try {
            $driver = config('cache.default', 'file');

            return $driver;
        } catch (\Exception $e) {
            return 'unknown';
        }
    }
}
