<?php

declare(strict_types=1);

namespace Infrastructure\FrameworkCore\Services\Health;

use Illuminate\Support\Facades\Log;
use Infrastructure\FrameworkCore\Contracts\Health\HealthCheckInterface;
use Infrastructure\FrameworkCore\Contracts\Health\HealthCheckResult;
use Infrastructure\FrameworkCore\Services\Health\Checks\CacheHealthCheck;
use Infrastructure\FrameworkCore\Services\Health\Checks\DatabaseHealthCheck;
use Infrastructure\FrameworkCore\Services\Health\Checks\QueueHealthCheck;
use Infrastructure\FrameworkCore\Services\Health\Checks\StorageHealthCheck;

class HealthCheckService
{
    protected array $checks = [];

    protected array $config;

    public function __construct()
    {
        $this->config = config('boundly.health', []);
        $this->registerDefaultChecks();
        $this->registerCustomChecks();
    }

    protected function registerDefaultChecks(): void
    {
        $services = $this->config['services'] ?? [];

        if ($services['database'] ?? true) {
            $this->register(new DatabaseHealthCheck);
        }

        if ($services['cache'] ?? true) {
            $this->register(new CacheHealthCheck);
        }

        if ($services['queue'] ?? true) {
            $this->register(new QueueHealthCheck);
        }

        if ($services['storage'] ?? true) {
            $this->register(new StorageHealthCheck);
        }
    }

    protected function registerCustomChecks(): void
    {
        $customClasses = $this->config['custom'] ?? [];

        foreach ($customClasses as $class) {
            if (is_string($class) && class_exists($class)) {
                $instance = new $class;
                if ($instance instanceof HealthCheckInterface) {
                    $this->register($instance);
                }
            }
        }
    }

    public function register(HealthCheckInterface $check): void
    {
        $this->checks[$check->name()] = $check;
    }

    public function unregister(string $name): void
    {
        unset($this->checks[$name]);
    }

    public function run(string $name): HealthCheckResult
    {
        if (! isset($this->checks[$name])) {
            return HealthCheckResult::unhealthy("Health check '{$name}' not found");
        }

        $check = $this->checks[$name];
        $timeout = $this->config['timeout'] ?? 5;

        try {
            $start = microtime(true);
            $result = $this->executeWithTimeout($check, $timeout);

            return $result;
        } catch (\Exception $e) {
            Log::warning("Health check '{$name}' failed: ".$e->getMessage());

            return HealthCheckResult::unhealthy($e->getMessage());
        }
    }

    public function runAll(): array
    {
        $results = [];

        foreach ($this->checks as $name => $check) {
            $results[$name] = $this->run($name);
        }

        return $results;
    }

    public function isHealthy(): bool
    {
        $results = $this->runAll();

        foreach ($results as $result) {
            if (! $result->healthy) {
                return false;
            }
        }

        return true;
    }

    public function getCriticalChecks(): array
    {
        $critical = [];

        foreach ($this->checks as $name => $check) {
            if ($check->severity() === 'critical') {
                $critical[$name] = $check;
            }
        }

        return $critical;
    }

    public function isCriticalHealthy(): bool
    {
        $results = $this->runAll();

        foreach ($this->getCriticalChecks() as $name => $check) {
            if (! $results[$name]->healthy) {
                return false;
            }
        }

        return true;
    }

    protected function executeWithTimeout(HealthCheckInterface $check, int $timeout): HealthCheckResult
    {
        $start = microtime(true);

        if (function_exists('set_time_limit')) {
            set_time_limit($timeout + 1);
        }

        $result = $check->check();

        $duration = microtime(true) - $start;

        if ($duration > $timeout) {
            Log::warning("Health check '{$check->name()}' exceeded timeout ({$duration}s > {$timeout}s)");
        }

        return $result;
    }

    public function getChecks(): array
    {
        return $this->checks;
    }

    public function getCheck(string $name): ?HealthCheckInterface
    {
        return $this->checks[$name] ?? null;
    }
}
