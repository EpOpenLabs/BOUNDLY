<?php

declare(strict_types=1);

namespace Infrastructure\FrameworkCore\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Infrastructure\FrameworkCore\Enums\LogLevel;
use Infrastructure\FrameworkCore\Enums\SecurityEvent;

class SecurityLogger
{
    protected array $config;

    protected bool $enabled;

    protected string $channel;

    protected array $excludedEvents;

    public function __construct(?array $config = null)
    {
        $this->config = $config ?? $this->getConfig();
        $this->enabled = $this->config['enabled'] ?? true;
        $this->channel = $this->config['channel'] ?? 'single';
        $this->excludedEvents = $this->config['excluded_events'] ?? [];
    }

    protected function getConfig(): array
    {
        if (function_exists('config') && app()->bound('config')) {
            return config('boundly.security_logging', []);
        }

        return [];
    }

    public function log(
        SecurityEvent $event,
        ?string $userId = null,
        ?string $ipAddress = null,
        ?Request $request = null,
        array $context = []
    ): void {
        if (! $this->enabled || $this->isExcluded($event)) {
            return;
        }

        $logContext = $this->buildContext($event, $userId, $ipAddress, $request, $context);
        $level = $this->getLogLevel($event);

        Log::channel($this->channel)->log($level->toPsr(), $this->buildMessage($event), $logContext);
    }

    public function logLoginSuccess(string $userId, ?Request $request = null, array $context = []): void
    {
        $this->log(SecurityEvent::LOGIN_SUCCESS, $userId, $this->getIp($request), $request, $context);
    }

    public function logLoginFailed(?string $identifier, ?Request $request = null, array $context = []): void
    {
        $this->log(SecurityEvent::LOGIN_FAILED, null, $this->getIp($request), $request, [
            'identifier' => $identifier,
            ...$context,
        ]);
    }

    public function logLogout(string $userId, ?Request $request = null, array $context = []): void
    {
        $this->log(SecurityEvent::LOGOUT, $userId, $this->getIp($request), $request, $context);
    }

    public function logTokenExpired(?string $userId, ?Request $request = null, array $context = []): void
    {
        $this->log(SecurityEvent::TOKEN_EXPIRED, $userId, $this->getIp($request), $request, $context);
    }

    public function logTokenInvalid(?Request $request = null, array $context = []): void
    {
        $this->log(SecurityEvent::TOKEN_INVALID, null, $this->getIp($request), $request, $context);
    }

    public function logRateLimitExceeded(?string $userId, ?Request $request = null, array $context = []): void
    {
        $this->log(SecurityEvent::RATE_LIMIT_EXCEEDED, $userId, $this->getIp($request), $request, $context);
    }

    public function logUnauthorizedAccess(?string $userId, ?Request $request = null, array $context = []): void
    {
        $this->log(SecurityEvent::UNAUTHORIZED_ACCESS, $userId, $this->getIp($request), $request, $context);
    }

    public function logForbiddenAccess(string $userId, ?Request $request = null, array $context = []): void
    {
        $this->log(SecurityEvent::FORBIDDEN_ACCESS, $userId, $this->getIp($request), $request, $context);
    }

    public function logBruteForceDetected(string $identifier, ?Request $request = null, array $context = []): void
    {
        $this->log(SecurityEvent::BRUTE_FORCE_DETECTED, null, $this->getIp($request), $request, [
            'identifier' => $identifier,
            'attempts' => $context['attempts'] ?? 1,
            ...$context,
        ]);
    }

    public function logBruteForceBlocked(string $identifier, ?Request $request = null, array $context = []): void
    {
        $this->log(SecurityEvent::BRUTE_FORCE_BLOCKED, null, $this->getIp($request), $request, [
            'identifier' => $identifier,
            'lockout_duration' => $context['lockout_duration'] ?? null,
            ...$context,
        ]);
    }

    public function logSuspiciousInput(?string $userId, ?Request $request = null, array $context = []): void
    {
        $this->log(SecurityEvent::SUSPICIOUS_INPUT, $userId, $this->getIp($request), $request, $context);
    }

    public function logApiKeyCreated(string $userId, string $keyId, ?Request $request = null): void
    {
        $this->log(SecurityEvent::API_KEY_CREATED, $userId, $this->getIp($request), $request, [
            'key_id' => $keyId,
        ]);
    }

    public function logApiKeyRevoked(string $userId, string $keyId, ?Request $request = null): void
    {
        $this->log(SecurityEvent::API_KEY_REVOKED, $userId, $this->getIp($request), $request, [
            'key_id' => $keyId,
        ]);
    }

    protected function isExcluded(SecurityEvent $event): bool
    {
        return in_array($event->value, $this->excludedEvents, true);
    }

    protected function buildContext(
        SecurityEvent $event,
        ?string $userId,
        ?string $ipAddress,
        ?Request $request,
        array $context
    ): array {
        $baseContext = [
            'event' => $event->value,
            'event_label' => $event->label(),
            'severity' => $event->severity()->value,
            'timestamp' => now()->toIso8601String(),
        ];

        if ($userId !== null) {
            $baseContext['user_id'] = $userId;
        }

        if ($ipAddress !== null) {
            $baseContext['ip_address'] = $ipAddress;
        }

        if ($request !== null) {
            $baseContext['request'] = [
                'method' => $request->method(),
                'path' => $request->path(),
                'user_agent' => $request->userAgent(),
                'content_type' => $request->getContentTypeFormat(),
            ];
        }

        return array_merge($baseContext, $context);
    }

    protected function buildMessage(SecurityEvent $event): string
    {
        return sprintf('[SECURITY] %s: %s', $event->value, $event->label());
    }

    protected function getLogLevel(SecurityEvent $event): LogLevel
    {
        return $event->severity();
    }

    protected function getIp(?Request $request): ?string
    {
        return $request?->ip();
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    public function setEnabled(bool $enabled): void
    {
        $this->enabled = $enabled;
    }
}
