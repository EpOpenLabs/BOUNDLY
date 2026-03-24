<?php

declare(strict_types=1);

namespace Infrastructure\FrameworkCore\Contracts\Health;

class HealthCheckResult
{
    public function __construct(
        public readonly bool $healthy,
        public readonly ?string $message = null,
        public readonly ?array $metadata = null,
        public readonly ?float $durationMs = null
    ) {}

    public static function healthy(?string $message = null, ?array $metadata = null, ?float $durationMs = null): self
    {
        return new self(healthy: true, message: $message, metadata: $metadata, durationMs: $durationMs);
    }

    public static function unhealthy(string $message, ?array $metadata = null, ?float $durationMs = null): self
    {
        return new self(healthy: false, message: $message, metadata: $metadata, durationMs: $durationMs);
    }

    public function toArray(): array
    {
        return [
            'healthy' => $this->healthy,
            'message' => $this->message,
            'metadata' => $this->metadata,
            'durationMs' => $this->durationMs,
        ];
    }
}
