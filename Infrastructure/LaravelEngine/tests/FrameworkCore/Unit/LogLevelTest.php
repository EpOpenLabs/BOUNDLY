<?php

namespace Tests\FrameworkCore\Unit;

use Infrastructure\FrameworkCore\Enums\LogLevel;
use PHPUnit\Framework\TestCase;

class LogLevelTest extends TestCase
{
    public function test_debug_level_value(): void
    {
        $level = LogLevel::DEBUG;

        $this->assertEquals('debug', $level->value);
        $this->assertEquals(8, $level->toPsr());
    }

    public function test_info_level_value(): void
    {
        $level = LogLevel::INFO;

        $this->assertEquals('info', $level->value);
        $this->assertEquals(7, $level->toPsr());
    }

    public function test_warning_level_value(): void
    {
        $level = LogLevel::WARNING;

        $this->assertEquals('warning', $level->value);
        $this->assertEquals(5, $level->toPsr());
    }

    public function test_error_level_value(): void
    {
        $level = LogLevel::ERROR;

        $this->assertEquals('error', $level->value);
        $this->assertEquals(4, $level->toPsr());
    }

    public function test_critical_level_value(): void
    {
        $level = LogLevel::CRITICAL;

        $this->assertEquals('critical', $level->value);
        $this->assertEquals(3, $level->toPsr());
    }

    public function test_alert_level_value(): void
    {
        $level = LogLevel::ALERT;

        $this->assertEquals('alert', $level->value);
        $this->assertEquals(2, $level->toPsr());
    }

    public function test_emergency_level_value(): void
    {
        $level = LogLevel::EMERGENCY;

        $this->assertEquals('emergency', $level->value);
        $this->assertEquals(1, $level->toPsr());
    }

    public function test_from_psr_debug(): void
    {
        $level = LogLevel::fromPsr(8);

        $this->assertEquals(LogLevel::DEBUG, $level);
    }

    public function test_from_psr_info(): void
    {
        $level = LogLevel::fromPsr(7);

        $this->assertEquals(LogLevel::INFO, $level);
    }

    public function test_from_psr_notice(): void
    {
        $level = LogLevel::fromPsr(6);

        $this->assertEquals(LogLevel::NOTICE, $level);
    }

    public function test_from_psr_warning(): void
    {
        $level = LogLevel::fromPsr(5);

        $this->assertEquals(LogLevel::WARNING, $level);
    }

    public function test_from_psr_error(): void
    {
        $level = LogLevel::fromPsr(4);

        $this->assertEquals(LogLevel::ERROR, $level);
    }

    public function test_from_psr_critical(): void
    {
        $level = LogLevel::fromPsr(3);

        $this->assertEquals(LogLevel::CRITICAL, $level);
    }

    public function test_from_psr_alert(): void
    {
        $level = LogLevel::fromPsr(2);

        $this->assertEquals(LogLevel::ALERT, $level);
    }

    public function test_from_psr_emergency(): void
    {
        $level = LogLevel::fromPsr(1);

        $this->assertEquals(LogLevel::EMERGENCY, $level);
    }

    public function test_all_levels_have_string_values(): void
    {
        foreach (LogLevel::cases() as $level) {
            $this->assertIsString($level->value);
            $this->assertNotEmpty($level->value);
        }
    }

    public function test_all_levels_have_psr_values(): void
    {
        foreach (LogLevel::cases() as $level) {
            $this->assertIsInt($level->toPsr());
            $this->assertGreaterThan(0, $level->toPsr());
            $this->assertLessThanOrEqual(8, $level->toPsr());
        }
    }
}
