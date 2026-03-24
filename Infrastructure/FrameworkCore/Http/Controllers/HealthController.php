<?php

declare(strict_types=1);

namespace Infrastructure\FrameworkCore\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Infrastructure\FrameworkCore\Enums\ErrorCode;
use Infrastructure\FrameworkCore\Services\Health\HealthCheckService;
use Infrastructure\FrameworkCore\Traits\ApiResponse;

class HealthController
{
    use ApiResponse;

    public function __construct(
        protected HealthCheckService $healthService
    ) {}

    public function check(): JsonResponse
    {
        $detailed = request()->query('detailed');

        if ($detailed) {
            return $this->detailedHealthCheck();
        }

        return $this->simpleHealthCheck();
    }

    protected function simpleHealthCheck(): JsonResponse
    {
        $isHealthy = $this->healthService->isHealthy();

        if ($isHealthy) {
            return $this->success([
                'status' => 'healthy',
                'timestamp' => now()->toIso8601String(),
            ]);
        }

        return $this->error(
            'Service is unhealthy',
            ErrorCode::SERVICE_UNAVAILABLE,
            503,
            ['status' => 'unhealthy']
        );
    }

    protected function detailedHealthCheck(): JsonResponse
    {
        $results = $this->healthService->runAll();
        $isHealthy = $this->healthService->isHealthy();

        $checks = [];
        $hasUnhealthy = false;

        foreach ($results as $name => $result) {
            if (! $result->healthy) {
                $hasUnhealthy = true;
            }

            $checks[$name] = [
                'status' => $result->healthy ? 'healthy' : 'unhealthy',
                'message' => $result->message,
                'duration_ms' => $result->durationMs,
            ];
        }

        $statusCode = $hasUnhealthy ? 503 : 200;
        $overallStatus = $hasUnhealthy ? 'unhealthy' : 'healthy';

        if (request()->query('critical_only')) {
            $checks = array_intersect_key($checks, $this->healthService->getCriticalChecks());
            $isHealthy = $this->healthService->isCriticalHealthy();
            $statusCode = $isHealthy ? 200 : 503;
            $overallStatus = $isHealthy ? 'healthy' : 'degraded';
        }

        $payload = [
            'status' => $overallStatus,
            'timestamp' => now()->toIso8601String(),
            'checks' => $checks,
        ];

        return response()->json($payload, $statusCode);
    }

    public function liveness(): JsonResponse
    {
        return $this->success([
            'status' => 'alive',
            'timestamp' => now()->toIso8601String(),
        ]);
    }

    public function readiness(): JsonResponse
    {
        $isReady = $this->healthService->isCriticalHealthy();

        if ($isReady) {
            return $this->success([
                'status' => 'ready',
                'timestamp' => now()->toIso8601String(),
            ]);
        }

        return $this->error(
            'Service is not ready',
            ErrorCode::SERVICE_UNAVAILABLE,
            503,
            ['status' => 'not_ready']
        );
    }
}
