<?php

namespace Tests\FrameworkCore\Unit;

use Illuminate\Http\Request;
use Infrastructure\FrameworkCore\Services\Security\TierBasedThrottlingService;
use Tests\FrameworkCore\FrameworkCoreTestCase;

class TierBasedThrottlingServiceTest extends FrameworkCoreTestCase
{
    public function test_can_be_instantiated(): void
    {
        $service = new TierBasedThrottlingService;
        $this->assertInstanceOf(TierBasedThrottlingService::class, $service);
    }

    public function test_get_tiers_returns_array(): void
    {
        $service = new TierBasedThrottlingService;
        $tiers = $service->getTiers();
        $this->assertIsArray($tiers);
        $this->assertArrayHasKey('free', $tiers);
    }

    public function test_get_tier_limits_returns_array(): void
    {
        $service = new TierBasedThrottlingService;
        $limits = $service->getTierLimits('free');
        $this->assertIsArray($limits);
        $this->assertArrayHasKey('requests_per_minute', $limits);
    }

    public function test_get_tier_limits_returns_free_for_unknown_tier(): void
    {
        $service = new TierBasedThrottlingService;
        $limits = $service->getTierLimits('unknown');
        $freeLimits = $service->getTierLimits('free');
        $this->assertEquals($freeLimits, $limits);
    }

    public function test_check_limit_returns_array(): void
    {
        $service = new TierBasedThrottlingService;
        $request = Request::create('/api/users', 'GET');

        $result = $service->checkLimit($request, 'free');
        $this->assertIsArray($result);
        $this->assertArrayHasKey('allowed', $result);
    }

    public function test_get_usage_returns_array(): void
    {
        $service = new TierBasedThrottlingService;
        $request = Request::create('/api/users', 'GET');

        $usage = $service->getUsage($request);
        $this->assertIsArray($usage);
        $this->assertArrayHasKey('minute', $usage);
        $this->assertArrayHasKey('hour', $usage);
        $this->assertArrayHasKey('day', $usage);
    }

    public function test_reset_limit_does_not_throw(): void
    {
        $service = new TierBasedThrottlingService;
        $request = Request::create('/api/users', 'GET');

        $service->resetLimit($request);
        $this->assertTrue(true);
    }
}
