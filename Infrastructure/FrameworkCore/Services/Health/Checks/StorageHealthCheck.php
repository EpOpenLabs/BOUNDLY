<?php

declare(strict_types=1);

namespace Infrastructure\FrameworkCore\Services\Health\Checks;

use Illuminate\Support\Facades\Storage;
use Infrastructure\FrameworkCore\Contracts\Health\HealthCheckInterface;
use Infrastructure\FrameworkCore\Contracts\Health\HealthCheckResult;

class StorageHealthCheck implements HealthCheckInterface
{
    public function name(): string
    {
        return 'storage';
    }

    public function severity(): string
    {
        return 'warning';
    }

    public function check(): HealthCheckResult
    {
        try {
            $start = microtime(true);

            $disk = Storage::disk();
            $testFile = 'health_check_'.uniqid().'.tmp';
            $testContent = 'test_'.time();

            $disk->put($testFile, $testContent);
            $retrieved = $disk->get($testFile);
            $disk->delete($testFile);

            $latency = round((microtime(true) - $start) * 1000, 2);

            if ($retrieved !== $testContent) {
                return HealthCheckResult::unhealthy('Storage read/write verification failed');
            }

            return HealthCheckResult::healthy('Storage is healthy', [
                'latency_ms' => $latency,
                'driver' => config('filesystems.default', 'local'),
            ]);
        } catch (\Exception $e) {
            return HealthCheckResult::unhealthy('Storage check failed: '.$e->getMessage(), [
                'driver' => config('filesystems.default', 'unknown'),
            ]);
        }
    }
}
