<?php

namespace Tests\FrameworkCore\Unit;

use Infrastructure\FrameworkCore\Attributes\Validation\JsonSchema;
use PHPUnit\Framework\TestCase;

class JsonSchemaAttributeTest extends TestCase
{
    public function test_default_values(): void
    {
        $schema = ['type' => 'object'];
        $attr = new JsonSchema($schema);

        $this->assertEquals($schema, $attr->getSchema());
        $this->assertFalse($attr->allowsAdditionalProperties());
    }

    public function test_custom_schema(): void
    {
        $schema = [
            'type' => 'object',
            'properties' => [
                'name' => ['type' => 'string'],
                'age' => ['type' => 'integer'],
            ],
            'required' => ['name'],
        ];

        $attr = new JsonSchema($schema);

        $this->assertEquals($schema, $attr->getSchema());
    }

    public function test_allow_additional_properties(): void
    {
        $schema = ['type' => 'object'];
        $attr = new JsonSchema($schema, allowAdditionalProperties: true);

        $this->assertTrue($attr->allowsAdditionalProperties());
    }

    public function test_disallow_additional_properties(): void
    {
        $schema = ['type' => 'object'];
        $attr = new JsonSchema($schema, allowAdditionalProperties: false);

        $this->assertFalse($attr->allowsAdditionalProperties());
    }

    public function test_complex_schema(): void
    {
        $schema = [
            '$schema' => 'http://json-schema.org/draft-07/schema#',
            'type' => 'object',
            'properties' => [
                'id' => ['type' => 'string', 'format' => 'uuid'],
                'email' => ['type' => 'string', 'format' => 'email'],
                'roles' => [
                    'type' => 'array',
                    'items' => ['type' => 'string'],
                ],
            ],
            'required' => ['id', 'email'],
        ];

        $attr = new JsonSchema($schema);

        $this->assertArrayHasKey('properties', $attr->getSchema());
        $this->assertArrayHasKey('required', $attr->getSchema());
    }
}
