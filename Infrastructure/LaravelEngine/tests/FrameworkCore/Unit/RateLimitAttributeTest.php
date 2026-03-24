<?php

namespace Tests\FrameworkCore\Unit;

use PHPUnit\Framework\TestCase;
use Infrastructure\FrameworkCore\Attributes\Behavior\RateLimit;

class RateLimitAttributeTest extends TestCase
{
    public function test_rate_limit_has_default_values(): void
    {
        $attr = new RateLimit();
        
        $this->assertEquals(60, $attr->maxAttempts);
        $this->assertEquals(1, $attr->decayMinutes);
        $this->assertNull($attr->prefix);
    }

    public function test_rate_limit_accepts_custom_values(): void
    {
        $attr = new RateLimit(maxAttempts: 100, decayMinutes: 5, prefix: 'custom');
        
        $this->assertEquals(100, $attr->maxAttempts);
        $this->assertEquals(5, $attr->decayMinutes);
        $this->assertEquals('custom', $attr->prefix);
    }

    public function test_rate_limit_can_set_only_max_attempts(): void
    {
        $attr = new RateLimit(maxAttempts: 30);
        
        $this->assertEquals(30, $attr->maxAttempts);
        $this->assertEquals(1, $attr->decayMinutes);
        $this->assertNull($attr->prefix);
    }

    public function test_rate_limit_can_set_only_decay_minutes(): void
    {
        $attr = new RateLimit(decayMinutes: 10);
        
        $this->assertEquals(60, $attr->maxAttempts);
        $this->assertEquals(10, $attr->decayMinutes);
        $this->assertNull($attr->prefix);
    }

    public function test_rate_limit_can_set_prefix_only(): void
    {
        $attr = new RateLimit(prefix: 'api:users');
        
        $this->assertEquals(60, $attr->maxAttempts);
        $this->assertEquals(1, $attr->decayMinutes);
        $this->assertEquals('api:users', $attr->prefix);
    }

    public function test_rate_limit_strict_limits(): void
    {
        $attr = new RateLimit(maxAttempts: 5, decayMinutes: 1);
        
        $this->assertEquals(5, $attr->maxAttempts);
    }

    public function test_rate_limit_lenient_limits(): void
    {
        $attr = new RateLimit(maxAttempts: 1000, decayMinutes: 60);
        
        $this->assertEquals(1000, $attr->maxAttempts);
        $this->assertEquals(60, $attr->decayMinutes);
    }
}
