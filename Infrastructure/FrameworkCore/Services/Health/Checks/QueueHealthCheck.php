<?php

declare(strict_types=1);

namespace Infrastructure\FrameworkCore\Services\Health\Checks;

use Illuminate\Support\Facades\Queue;
use Infrastructure\FrameworkCore\Contracts\Health\HealthCheckInterface;
use Infrastructure\FrameworkCore\Contracts\Health\HealthCheckResult;

class QueueHealthCheck implements HealthCheckInterface
{
    public function name(): string
    {
        return 'queue';
    }

    public function severity(): string
    {
        return 'warning';
    }

    public function check(): HealthCheckResult
    {
        try {
            $start = microtime(true);

            Queue::size('default');

            $latency = round((microtime(true) - $start) * 1000, 2);

            return HealthCheckResult::healthy('Queue is healthy', [
                'latency_ms' => $latency,
                'driver' => config('queue.default', 'sync'),
            ]);
        } catch (\Exception $e) {
            return HealthCheckResult::unhealthy('Queue check failed: '.$e->getMessage(), [
                'driver' => config('queue.default', 'unknown'),
            ]);
        }
    }
}
