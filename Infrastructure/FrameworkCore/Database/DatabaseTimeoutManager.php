<?php

declare(strict_types=1);

namespace Infrastructure\FrameworkCore\Database;

use Illuminate\Support\Facades\DB;

class DatabaseTimeoutManager
{
    protected array $config;
    protected int $defaultTimeout;

    public function __construct()
    {
        $this->config = config('boundly.database_timeouts', []);
        $this->defaultTimeout = $this->config['default'] ?? 30;
    }

    public function getDefaultTimeout(): int
    {
        return $this->defaultTimeout;
    }

    public function getTimeout(string $operation): int
    {
        return $this->config['operations'][$operation] ?? $this->defaultTimeout;
    }

    public function applyTimeout(string $connection = 'default'): void
    {
        $timeout = $this->getTimeout('default');
        $this->setStatementTimeout($connection, $timeout);
    }

    public function applyOperationTimeout(string $operation, string $connection = 'default'): void
    {
        $timeout = $this->getTimeout($operation);
        $this->setStatementTimeout($connection, $timeout);
    }

    protected function setStatementTimeout(string $connection, int $seconds): void
    {
        $driver = DB::connection($connection)->getDriverName();

        match ($driver) {
            'mysql' => DB::connection($connection)->statement("SET SESSION wait_timeout = {$seconds}"),
            'pgsql' => DB::connection($connection)->statement("SET statement_timeout = '{$seconds}s'"),
            'sqlite' => null,
            default => null,
        };
    }

    public function wrapWithTimeout(callable $callback, ?string $operation = null): mixed
    {
        $timeout = $operation ? $this->getTimeout($operation) : $this->defaultTimeout;
        $driver = DB::connection()->getDriverName();

        if ($driver === 'mysql') {
            DB::statement("SET SESSION wait_timeout = {$timeout}");
        } elseif ($driver === 'pgsql') {
            DB::statement("SET statement_timeout = '{$timeout}s'");
        }

        return $callback();
    }

    public function getOperationTimeout(string $operation): int
    {
        return $this->getTimeout($operation);
    }
}
