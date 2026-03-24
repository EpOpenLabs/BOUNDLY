<?php

namespace Tests\FrameworkCore\Unit;

use Illuminate\Cache\RateLimiter;
use Infrastructure\FrameworkCore\Services\BruteForceProtectionService;
use Infrastructure\FrameworkCore\Services\SecurityLogger;
use PHPUnit\Framework\TestCase;

class BruteForceProtectionServiceTest extends TestCase
{
    public function test_is_enabled_by_default(): void
    {
        $service = new BruteForceProtectionService(
            $this->createMock(RateLimiter::class),
            new SecurityLogger(['enabled' => false])
        );

        $this->assertTrue($service->isEnabled());
    }

    public function test_get_max_attempts_returns_default(): void
    {
        $service = new BruteForceProtectionService(
            $this->createMock(RateLimiter::class),
            new SecurityLogger(['enabled' => false])
        );

        $this->assertEquals(5, $service->getMaxAttempts());
    }

    public function test_get_decay_minutes_returns_default(): void
    {
        $service = new BruteForceProtectionService(
            $this->createMock(RateLimiter::class),
            new SecurityLogger(['enabled' => false])
        );

        $this->assertEquals(15, $service->getDecayMinutes());
    }

    public function test_get_lockout_multiplier_returns_default(): void
    {
        $service = new BruteForceProtectionService(
            $this->createMock(RateLimiter::class),
            new SecurityLogger(['enabled' => false])
        );

        $this->assertEquals(2, $service->getLockoutMultiplier());
    }

    public function test_get_max_lockouts_returns_default(): void
    {
        $service = new BruteForceProtectionService(
            $this->createMock(RateLimiter::class),
            new SecurityLogger(['enabled' => false])
        );

        $this->assertEquals(3, $service->getMaxLockouts());
    }

    public function test_get_track_by_returns_default(): void
    {
        $service = new BruteForceProtectionService(
            $this->createMock(RateLimiter::class),
            new SecurityLogger(['enabled' => false])
        );

        $this->assertEquals('email', $service->getTrackBy());
    }

    public function test_get_key_format(): void
    {
        $service = new BruteForceProtectionService(
            $this->createMock(RateLimiter::class),
            new SecurityLogger(['enabled' => false])
        );

        $key = $service->getKey('test@example.com');

        $this->assertEquals('brute_force:test@example.com', $key);
    }

    public function test_get_lockout_key_format(): void
    {
        $service = new BruteForceProtectionService(
            $this->createMock(RateLimiter::class),
            new SecurityLogger(['enabled' => false])
        );

        $key = $service->getLockoutKey('test@example.com');

        $this->assertEquals('brute_force_lockout:test@example.com', $key);
    }

    public function test_too_many_attempts_delegates_to_limiter(): void
    {
        $limiter = $this->createMock(RateLimiter::class);
        $limiter->expects($this->once())
            ->method('tooManyAttempts')
            ->with('brute_force:test@example.com', 5)
            ->willReturn(true);

        $service = new BruteForceProtectionService(
            $limiter,
            new SecurityLogger(['enabled' => false])
        );

        $this->assertTrue($service->tooManyAttempts('test@example.com'));
    }

    public function test_hits_delegates_to_limiter(): void
    {
        $limiter = $this->createMock(RateLimiter::class);
        $limiter->expects($this->once())
            ->method('hit')
            ->with('brute_force:test@example.com', 900);

        $service = new BruteForceProtectionService(
            $limiter,
            new SecurityLogger(['enabled' => false])
        );

        $service->hits('test@example.com');
    }

    public function test_attempts_delegates_to_limiter(): void
    {
        $limiter = $this->createMock(RateLimiter::class);
        $limiter->expects($this->once())
            ->method('attempts')
            ->with('brute_force:test@example.com')
            ->willReturn(3);

        $service = new BruteForceProtectionService(
            $limiter,
            new SecurityLogger(['enabled' => false])
        );

        $this->assertEquals(3, $service->attempts('test@example.com'));
    }

    public function test_available_in_delegates_to_limiter(): void
    {
        $limiter = $this->createMock(RateLimiter::class);
        $limiter->expects($this->once())
            ->method('availableIn')
            ->with('brute_force:test@example.com')
            ->willReturn(300);

        $service = new BruteForceProtectionService(
            $limiter,
            new SecurityLogger(['enabled' => false])
        );

        $this->assertEquals(300, $service->availableIn('test@example.com'));
    }

    public function test_clear_resets_attempts(): void
    {
        $limiter = $this->createMock(RateLimiter::class);
        $limiter->expects($this->once())
            ->method('resetAttempts')
            ->with('brute_force:test@example.com');

        $service = new BruteForceProtectionService(
            $limiter,
            new SecurityLogger(['enabled' => false])
        );

        $service->clear('test@example.com');
    }

    public function test_remaining_attempts_calculation(): void
    {
        $limiter = $this->createMock(RateLimiter::class);
        $limiter->method('attempts')
            ->with('brute_force:test@example.com')
            ->willReturn(3);

        $service = new BruteForceProtectionService(
            $limiter,
            new SecurityLogger(['enabled' => false])
        );

        $this->assertEquals(2, $service->remainingAttempts('test@example.com'));
    }

    public function test_remaining_attempts_never_negative(): void
    {
        $limiter = $this->createMock(RateLimiter::class);
        $limiter->method('attempts')
            ->with('brute_force:test@example.com')
            ->willReturn(10);

        $service = new BruteForceProtectionService(
            $limiter,
            new SecurityLogger(['enabled' => false])
        );

        $this->assertEquals(0, $service->remainingAttempts('test@example.com'));
    }
}
