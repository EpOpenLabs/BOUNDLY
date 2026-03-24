<?php

namespace Tests\FrameworkCore\Unit;

use Infrastructure\FrameworkCore\Attributes\Behavior\ThrottleLogin;
use PHPUnit\Framework\TestCase;

class ThrottleLoginAttributeTest extends TestCase
{
    public function test_default_values(): void
    {
        $attr = new ThrottleLogin();

        $this->assertEquals(5, $attr->maxAttempts);
        $this->assertEquals(15, $attr->decayMinutes);
        $this->assertEquals('email', $attr->trackBy);
        $this->assertTrue($attr->lockoutEnabled);
        $this->assertEquals(2, $attr->lockoutMultiplier);
        $this->assertEquals(3, $attr->maxLockouts);
    }

    public function test_custom_max_attempts(): void
    {
        $attr = new ThrottleLogin(maxAttempts: 10);

        $this->assertEquals(10, $attr->maxAttempts);
    }

    public function test_custom_decay_minutes(): void
    {
        $attr = new ThrottleLogin(decayMinutes: 30);

        $this->assertEquals(30, $attr->decayMinutes);
    }

    public function test_custom_track_by(): void
    {
        $attr = new ThrottleLogin(trackBy: 'username');

        $this->assertEquals('username', $attr->trackBy);
    }

    public function test_lockout_disabled(): void
    {
        $attr = new ThrottleLogin(lockoutEnabled: false);

        $this->assertFalse($attr->lockoutEnabled);
    }

    public function test_custom_lockout_multiplier(): void
    {
        $attr = new ThrottleLogin(lockoutMultiplier: 3);

        $this->assertEquals(3, $attr->lockoutMultiplier);
    }

    public function test_custom_max_lockouts(): void
    {
        $attr = new ThrottleLogin(maxLockouts: 5);

        $this->assertEquals(5, $attr->maxLockouts);
    }

    public function test_all_custom_values(): void
    {
        $attr = new ThrottleLogin(
            maxAttempts: 3,
            decayMinutes: 5,
            trackBy: 'phone',
            lockoutEnabled: true,
            lockoutMultiplier: 4,
            maxLockouts: 2
        );

        $this->assertEquals(3, $attr->maxAttempts);
        $this->assertEquals(5, $attr->decayMinutes);
        $this->assertEquals('phone', $attr->trackBy);
        $this->assertTrue($attr->lockoutEnabled);
        $this->assertEquals(4, $attr->lockoutMultiplier);
        $this->assertEquals(2, $attr->maxLockouts);
    }
}
