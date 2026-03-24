<?php

declare(strict_types=1);

namespace Infrastructure\FrameworkCore\Services\Health\Checks;

use Illuminate\Support\Facades\DB;
use Infrastructure\FrameworkCore\Contracts\Health\HealthCheckInterface;
use Infrastructure\FrameworkCore\Contracts\Health\HealthCheckResult;

class DatabaseHealthCheck implements HealthCheckInterface
{
    public function name(): string
    {
        return 'database';
    }

    public function severity(): string
    {
        return 'critical';
    }

    public function check(): HealthCheckResult
    {
        try {
            $start = microtime(true);

            DB::connection()->getPdo();
            DB::select('SELECT 1');

            $latency = round((microtime(true) - $start) * 1000, 2);

            return HealthCheckResult::healthy('Database connection is healthy', [
                'latency_ms' => $latency,
                'driver' => DB::connection()->getDriverName(),
            ]);
        } catch (\Exception $e) {
            return HealthCheckResult::unhealthy('Database connection failed: '.$e->getMessage(), [
                'driver' => $this->getDriverName(),
            ]);
        }
    }

    protected function getDriverName(): string
    {
        try {
            return DB::connection()->getDriverName();
        } catch (\Exception $e) {
            return 'unknown';
        }
    }
}
