<?php

declare(strict_types=1);

namespace Infrastructure\FrameworkCore\Contracts\Health;

interface HealthCheckInterface
{
    public function name(): string;

    public function check(): HealthCheckResult;

    public function severity(): string;
}
