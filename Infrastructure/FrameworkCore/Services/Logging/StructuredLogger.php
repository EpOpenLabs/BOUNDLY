<?php

declare(strict_types=1);

namespace Infrastructure\FrameworkCore\Services\Logging;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class StructuredLogger
{
    protected string $channel;

    protected string $appName;

    protected string $environment;

    protected array $defaultContext;

    public function __construct()
    {
        $this->channel = config('boundly.logging.channel', 'single');
        $this->appName = config('app.name', 'BOUNDLY');
        $this->environment = config('app.env', 'production');
        $this->defaultContext = [
            'app' => $this->appName,
            'env' => $this->environment,
            'version' => config('boundly.version', '1.0.0'),
        ];
    }

    public function emergency(string $message, array $context = []): void
    {
        $this->log('emergency', $message, $context);
    }

    public function alert(string $message, array $context = []): void
    {
        $this->log('alert', $message, $context);
    }

    public function critical(string $message, array $context = []): void
    {
        $this->log('critical', $message, $context);
    }

    public function error(string $message, array $context = []): void
    {
        $this->log('error', $message, $context);
    }

    public function warning(string $message, array $context = []): void
    {
        $this->log('warning', $message, $context);
    }

    public function notice(string $message, array $context = []): void
    {
        $this->log('notice', $message, $context);
    }

    public function info(string $message, array $context = []): void
    {
        $this->log('info', $message, $context);
    }

    public function debug(string $message, array $context = []): void
    {
        $this->log('debug', $message, $context);
    }

    protected function log(string $level, string $message, array $context): void
    {
        $payload = array_merge($this->defaultContext, [
            'level' => $level,
            'message' => $message,
            'timestamp' => now()->toIso8601String(),
            'context' => $context,
        ]);

        Log::channel($this->channel)->log($level, $message, $payload);
    }

    public function withRequest(Request $request, array $additional = []): RequestLogBuilder
    {
        return new RequestLogBuilder($this, $request, $additional);
    }

    public function withUser(?string $userId, array $additional = []): UserLogBuilder
    {
        return new UserLogBuilder($this, $userId, $additional);
    }

    public function withContext(array $context): ContextLogBuilder
    {
        return new ContextLogBuilder($this, $context);
    }

    public function channel(string $channel): self
    {
        $clone = clone $this;
        $clone->channel = $channel;

        return $clone;
    }
}

class RequestLogBuilder
{
    public function __construct(
        protected StructuredLogger $logger,
        protected Request $request,
        protected array $additional = []
    ) {}

    public function info(string $message): void
    {
        $this->logger->info($message, $this->buildContext());
    }

    public function error(string $message): void
    {
        $this->logger->error($message, $this->buildContext());
    }

    public function warning(string $message): void
    {
        $this->logger->warning($message, $this->buildContext());
    }

    protected function buildContext(): array
    {
        return array_merge([
            'request' => [
                'id' => $this->request->header('X-Request-ID', uniqid('req_')),
                'method' => $this->request->method(),
                'path' => $this->request->path(),
                'ip' => $this->request->ip(),
                'user_agent' => $this->request->userAgent(),
            ],
        ], $this->additional);
    }
}

class UserLogBuilder
{
    public function __construct(
        protected StructuredLogger $logger,
        protected ?string $userId,
        protected array $additional = []
    ) {}

    public function info(string $message): void
    {
        $this->logger->info($message, $this->buildContext());
    }

    public function error(string $message): void
    {
        $this->logger->error($message, $this->buildContext());
    }

    protected function buildContext(): array
    {
        return array_merge([
            'user' => [
                'id' => $this->userId,
            ],
        ], $this->additional);
    }
}

class ContextLogBuilder
{
    public function __construct(
        protected StructuredLogger $logger,
        protected array $context = []
    ) {}

    public function info(string $message): void
    {
        $this->logger->info($message, $this->context);
    }

    public function error(string $message): void
    {
        $this->logger->error($message, $this->context);
    }

    public function warning(string $message): void
    {
        $this->logger->warning($message, $this->context);
    }

    public function debug(string $message): void
    {
        $this->logger->debug($message, $this->context);
    }
}
