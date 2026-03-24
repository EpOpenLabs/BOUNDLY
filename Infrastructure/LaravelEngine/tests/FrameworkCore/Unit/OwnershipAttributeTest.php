<?php

namespace Tests\FrameworkCore\Unit;

use Infrastructure\FrameworkCore\Attributes\Behavior\Ownership;
use PHPUnit\Framework\TestCase;

class OwnershipAttributeTest extends TestCase
{
    public function test_default_values(): void
    {
        $attr = new Ownership;

        $this->assertEquals('user_id', $attr->getOwnerField());
        $this->assertTrue($attr->allowsAdminBypass());
        $this->assertNull($attr->getResourceField());
    }

    public function test_custom_owner_field(): void
    {
        $attr = new Ownership(ownerField: 'owner_id');

        $this->assertEquals('owner_id', $attr->getOwnerField());
    }

    public function test_disallow_admin_bypass(): void
    {
        $attr = new Ownership(allowAdminBypass: false);

        $this->assertFalse($attr->allowsAdminBypass());
    }

    public function test_custom_resource_field(): void
    {
        $attr = new Ownership(resourceField: 'project_id');

        $this->assertEquals('project_id', $attr->getResourceField());
    }

    public function test_all_custom_values(): void
    {
        $attr = new Ownership(
            ownerField: 'author_id',
            allowAdminBypass: false,
            resourceField: 'document_id'
        );

        $this->assertEquals('author_id', $attr->getOwnerField());
        $this->assertFalse($attr->allowsAdminBypass());
        $this->assertEquals('document_id', $attr->getResourceField());
    }

    public function test_public_properties_accessible(): void
    {
        $attr = new Ownership(
            ownerField: 'creator_id',
            allowAdminBypass: true,
            resourceField: 'item_id'
        );

        $this->assertEquals('creator_id', $attr->ownerField);
        $this->assertTrue($attr->allowAdminBypass);
        $this->assertEquals('item_id', $attr->resourceField);
    }
}
