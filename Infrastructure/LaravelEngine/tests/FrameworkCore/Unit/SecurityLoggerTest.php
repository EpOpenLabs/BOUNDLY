<?php

namespace Tests\FrameworkCore\Unit;

use Infrastructure\FrameworkCore\Enums\SecurityEvent;
use Infrastructure\FrameworkCore\Services\SecurityLogger;
use PHPUnit\Framework\TestCase;

class SecurityLoggerTest extends TestCase
{
    public function test_logger_is_enabled_by_default(): void
    {
        $logger = new SecurityLogger;

        $this->assertTrue($logger->isEnabled());
    }

    public function test_logger_can_be_disabled(): void
    {
        $logger = new SecurityLogger;
        $logger->setEnabled(false);

        $this->assertFalse($logger->isEnabled());
    }

    public function test_logger_can_be_re_enabled(): void
    {
        $logger = new SecurityLogger;
        $logger->setEnabled(false);
        $logger->setEnabled(true);

        $this->assertTrue($logger->isEnabled());
    }

    public function test_log_method_does_not_throw_when_disabled(): void
    {
        $logger = new SecurityLogger;
        $logger->setEnabled(false);

        $logger->log(SecurityEvent::LOGIN_FAILED, null, '127.0.0.1');

        $this->assertFalse($logger->isEnabled());
    }

    public function test_convenience_methods_exist(): void
    {
        $logger = new SecurityLogger;

        $this->assertTrue(method_exists($logger, 'logLoginSuccess'));
        $this->assertTrue(method_exists($logger, 'logLoginFailed'));
        $this->assertTrue(method_exists($logger, 'logLogout'));
        $this->assertTrue(method_exists($logger, 'logTokenExpired'));
        $this->assertTrue(method_exists($logger, 'logTokenInvalid'));
        $this->assertTrue(method_exists($logger, 'logRateLimitExceeded'));
        $this->assertTrue(method_exists($logger, 'logUnauthorizedAccess'));
        $this->assertTrue(method_exists($logger, 'logForbiddenAccess'));
        $this->assertTrue(method_exists($logger, 'logBruteForceDetected'));
        $this->assertTrue(method_exists($logger, 'logBruteForceBlocked'));
        $this->assertTrue(method_exists($logger, 'logSuspiciousInput'));
        $this->assertTrue(method_exists($logger, 'logApiKeyCreated'));
        $this->assertTrue(method_exists($logger, 'logApiKeyRevoked'));
    }

    public function test_log_does_not_throw_when_disabled(): void
    {
        $logger = new SecurityLogger(['enabled' => false]);

        $logger->log(SecurityEvent::UNAUTHORIZED_ACCESS, 'user-123', '192.168.1.1');

        $this->assertFalse($logger->isEnabled());
    }

    public function test_excluded_events_are_not_logged(): void
    {
        $logger = new SecurityLogger([
            'enabled' => true,
            'excluded_events' => ['auth.login.success'],
        ]);

        $logger->log(SecurityEvent::LOGIN_SUCCESS, 'user-123');

        $this->assertTrue($logger->isEnabled());
    }
}
