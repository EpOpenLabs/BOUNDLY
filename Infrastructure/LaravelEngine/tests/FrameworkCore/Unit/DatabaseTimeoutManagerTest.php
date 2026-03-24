<?php

namespace Tests\FrameworkCore\Unit;

use Infrastructure\FrameworkCore\Database\DatabaseTimeoutManager;
use Tests\FrameworkCore\FrameworkCoreTestCase;

class DatabaseTimeoutManagerTest extends FrameworkCoreTestCase
{
    public function test_can_be_instantiated(): void
    {
        $manager = new DatabaseTimeoutManager;
        $this->assertInstanceOf(DatabaseTimeoutManager::class, $manager);
    }

    public function test_get_default_timeout_returns_int(): void
    {
        $manager = new DatabaseTimeoutManager;
        $timeout = $manager->getDefaultTimeout();
        $this->assertIsInt($timeout);
    }

    public function test_get_operation_timeout_returns_int(): void
    {
        $manager = new DatabaseTimeoutManager;
        $timeout = $manager->getOperationTimeout('select');
        $this->assertIsInt($timeout);
    }

    public function test_get_unknown_operation_returns_default(): void
    {
        $manager = new DatabaseTimeoutManager;
        $timeout = $manager->getOperationTimeout('unknown_operation');
        $this->assertEquals($manager->getDefaultTimeout(), $timeout);
    }
}
