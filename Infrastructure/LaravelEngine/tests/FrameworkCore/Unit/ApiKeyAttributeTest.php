<?php

namespace Tests\FrameworkCore\Unit;

use Infrastructure\FrameworkCore\Attributes\Security\ApiKey;
use PHPUnit\Framework\TestCase;

class ApiKeyAttributeTest extends TestCase
{
    public function test_default_values(): void
    {
        $attr = new ApiKey();

        $this->assertEquals('X-Api-Key', $attr->header);
        $this->assertEquals([], $attr->scopes);
        $this->assertTrue($attr->required);
        $this->assertNull($attr->description);
    }

    public function test_custom_header(): void
    {
        $attr = new ApiKey(header: 'Authorization');

        $this->assertEquals('Authorization', $attr->header);
    }

    public function test_custom_scopes(): void
    {
        $attr = new ApiKey(scopes: ['read', 'write']);

        $this->assertEquals(['read', 'write'], $attr->scopes);
    }

    public function test_not_required(): void
    {
        $attr = new ApiKey(required: false);

        $this->assertFalse($attr->required);
    }

    public function test_with_description(): void
    {
        $attr = new ApiKey(description: 'API key for external services');

        $this->assertEquals('API key for external services', $attr->description);
    }

    public function test_all_custom_values(): void
    {
        $attr = new ApiKey(
            header: 'X-Custom-Key',
            scopes: ['admin', 'api:read', 'api:write'],
            required: true,
            description: 'Production API key'
        );

        $this->assertEquals('X-Custom-Key', $attr->header);
        $this->assertCount(3, $attr->scopes);
        $this->assertTrue($attr->required);
        $this->assertEquals('Production API key', $attr->description);
    }
}
