<?php

namespace Tests\FrameworkCore\Unit;

use Illuminate\Http\Request;
use Infrastructure\FrameworkCore\Services\Security\IpAccessControl;
use Tests\FrameworkCore\FrameworkCoreTestCase;

class IpAccessControlTest extends FrameworkCoreTestCase
{
    public function test_ip_access_control_can_be_instantiated(): void
    {
        $control = new IpAccessControl;
        $this->assertInstanceOf(IpAccessControl::class, $control);
    }

    public function test_is_enabled_returns_bool(): void
    {
        $control = new IpAccessControl;
        $this->assertIsBool($control->isEnabled());
    }

    public function test_is_allowed_returns_true_when_disabled(): void
    {
        $control = new IpAccessControl;
        $this->assertTrue($control->isAllowed('192.168.1.1'));
    }

    public function test_is_blacklisted_returns_false_when_empty(): void
    {
        $control = new IpAccessControl;
        $this->assertFalse($control->isBlacklisted('192.168.1.1'));
    }

    public function test_is_whitelisted_returns_false_when_empty(): void
    {
        $control = new IpAccessControl;
        $this->assertFalse($control->isWhitelisted('192.168.1.1'));
    }

    public function test_get_whitelist_returns_array(): void
    {
        $control = new IpAccessControl;
        $whitelist = $control->getWhitelist();
        $this->assertIsArray($whitelist);
    }

    public function test_get_blacklist_returns_array(): void
    {
        $control = new IpAccessControl;
        $blacklist = $control->getBlacklist();
        $this->assertIsArray($blacklist);
    }

    public function test_check_request_returns_bool(): void
    {
        $control = new IpAccessControl;
        $request = Request::create('/api/users', 'GET');

        $result = $control->checkRequest($request);
        $this->assertIsBool($result);
    }
}
