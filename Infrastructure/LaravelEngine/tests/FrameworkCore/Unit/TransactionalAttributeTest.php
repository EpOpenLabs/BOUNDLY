<?php

namespace Tests\FrameworkCore\Unit;

use Infrastructure\FrameworkCore\Attributes\Behavior\Transactional;
use PHPUnit\Framework\TestCase;

class TransactionalAttributeTest extends TestCase
{
    public function test_default_values(): void
    {
        $attr = new Transactional();

        $this->assertEquals(1, $attr->getTries());
        $this->assertEquals(60, $attr->getTimeout());
        $this->assertTrue($attr->allowsNested());
    }

    public function test_custom_tries(): void
    {
        $attr = new Transactional(tries: 3);

        $this->assertEquals(3, $attr->getTries());
    }

    public function test_custom_timeout(): void
    {
        $attr = new Transactional(timeout: 120);

        $this->assertEquals(120, $attr->getTimeout());
    }

    public function test_nested_disabled(): void
    {
        $attr = new Transactional(nested: false);

        $this->assertFalse($attr->allowsNested());
    }

    public function test_all_custom_values(): void
    {
        $attr = new Transactional(
            tries: 5,
            timeout: 180,
            nested: false
        );

        $this->assertEquals(5, $attr->getTries());
        $this->assertEquals(180, $attr->getTimeout());
        $this->assertFalse($attr->allowsNested());
    }

    public function test_public_properties_accessible(): void
    {
        $attr = new Transactional(
            tries: 3,
            timeout: 90,
            nested: true
        );

        $this->assertEquals(3, $attr->tries);
        $this->assertEquals(90, $attr->timeout);
        $this->assertTrue($attr->nested);
    }
}
