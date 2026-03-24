<?php

namespace Tests\FrameworkCore\Unit;

use PHPUnit\Framework\TestCase;
use Infrastructure\FrameworkCore\Registry\EntityRegistry;

class EntityValidatorTest extends TestCase
{
    private EntityRegistry $registry;

    protected function setUp(): void
    {
        parent::setUp();
        $this->registry = new EntityRegistry();
    }

    public function test_registry_is_instantiable(): void
    {
        $this->assertInstanceOf(EntityRegistry::class, $this->registry);
    }

    public function test_registry_starts_empty(): void
    {
        $this->assertEmpty($this->registry->getAllEntities());
    }
}
