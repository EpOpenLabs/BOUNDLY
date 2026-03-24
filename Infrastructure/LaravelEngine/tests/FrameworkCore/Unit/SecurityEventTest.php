<?php

namespace Tests\FrameworkCore\Unit;

use Infrastructure\FrameworkCore\Enums\LogLevel;
use Infrastructure\FrameworkCore\Enums\SecurityEvent;
use PHPUnit\Framework\TestCase;

class SecurityEventTest extends TestCase
{
    public function test_login_success_event_value(): void
    {
        $event = SecurityEvent::LOGIN_SUCCESS;

        $this->assertEquals('auth.login.success', $event->value);
    }

    public function test_login_failed_event_value(): void
    {
        $event = SecurityEvent::LOGIN_FAILED;

        $this->assertEquals('auth.login.failed', $event->value);
    }

    public function test_login_success_has_info_severity(): void
    {
        $event = SecurityEvent::LOGIN_SUCCESS;

        $this->assertEquals(LogLevel::INFO, $event->severity());
    }

    public function test_login_failed_has_warning_severity(): void
    {
        $event = SecurityEvent::LOGIN_FAILED;

        $this->assertEquals(LogLevel::WARNING, $event->severity());
    }

    public function test_suspicious_input_has_critical_severity(): void
    {
        $event = SecurityEvent::SUSPICIOUS_INPUT;

        $this->assertEquals(LogLevel::CRITICAL, $event->severity());
    }

    public function test_brute_force_events_have_alert_severity(): void
    {
        $detected = SecurityEvent::BRUTE_FORCE_DETECTED;
        $blocked = SecurityEvent::BRUTE_FORCE_BLOCKED;

        $this->assertEquals(LogLevel::ALERT, $detected->severity());
        $this->assertEquals(LogLevel::ALERT, $blocked->severity());
    }

    public function test_event_labels_are_descriptive(): void
    {
        $this->assertEquals('Login Success', SecurityEvent::LOGIN_SUCCESS->label());
        $this->assertEquals('Login Failed', SecurityEvent::LOGIN_FAILED->label());
        $this->assertEquals('Suspicious Input Detected', SecurityEvent::SUSPICIOUS_INPUT->label());
        $this->assertEquals('Brute Force Attack Detected', SecurityEvent::BRUTE_FORCE_DETECTED->label());
    }

    public function test_all_events_have_string_values(): void
    {
        foreach (SecurityEvent::cases() as $event) {
            $this->assertIsString($event->value);
            $this->assertNotEmpty($event->value);
        }
    }

    public function test_logout_has_info_severity(): void
    {
        $event = SecurityEvent::LOGOUT;

        $this->assertEquals(LogLevel::INFO, $event->severity());
    }

    public function test_rate_limit_exceeded_has_warning_severity(): void
    {
        $event = SecurityEvent::RATE_LIMIT_EXCEEDED;

        $this->assertEquals(LogLevel::WARNING, $event->severity());
    }
}
