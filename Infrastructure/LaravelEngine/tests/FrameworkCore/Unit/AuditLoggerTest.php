<?php

namespace Tests\FrameworkCore\Unit;

use Infrastructure\FrameworkCore\Services\Logging\AuditLogger;
use Tests\FrameworkCore\FrameworkCoreTestCase;

class AuditLoggerTest extends FrameworkCoreTestCase
{
    public function test_is_enabled_returns_bool(): void
    {
        $logger = new AuditLogger;
        $this->assertIsBool($logger->isEnabled());
    }

    public function test_audit_logger_can_be_instantiated(): void
    {
        $logger = new AuditLogger;
        $this->assertInstanceOf(AuditLogger::class, $logger);
    }
}
