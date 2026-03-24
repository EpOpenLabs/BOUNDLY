<?php

namespace Tests\FrameworkCore\Unit;

use Infrastructure\FrameworkCore\Attributes\Relations\BelongsTo;
use Infrastructure\FrameworkCore\Attributes\Relations\HasMany;
use Infrastructure\FrameworkCore\Attributes\Relations\ManyToMany;
use Infrastructure\FrameworkCore\Attributes\Schema\Column;
use Infrastructure\FrameworkCore\Attributes\Schema\Entity;
use Infrastructure\FrameworkCore\Attributes\Schema\Id;
use PHPUnit\Framework\TestCase;

class SchemaAttributesTest extends TestCase
{
    public function test_entity_attribute(): void
    {
        $entity = new Entity(table: 'users', resource: 'users', morphName: 'user');

        $this->assertEquals('users', $entity->table);
        $this->assertEquals('users', $entity->resource);
        $this->assertEquals('user', $entity->morphName);
    }

    public function test_entity_with_nullable_resource(): void
    {
        $entity = new Entity(table: 'posts');

        $this->assertEquals('posts', $entity->table);
        $this->assertNull($entity->resource);
        $this->assertNull($entity->morphName);
    }

    public function test_column_attribute_defaults(): void
    {
        $column = new Column;

        $this->assertEquals('string', $column->type);
        $this->assertNull($column->length);
        $this->assertFalse($column->nullable);
        $this->assertNull($column->default);
        $this->assertEmpty($column->roles);
    }

    public function test_column_with_all_options(): void
    {
        $column = new Column(
            type: 'decimal(10,2)',
            length: null,
            nullable: true,
            default: 0.00,
            roles: ['admin']
        );

        $this->assertEquals('decimal(10,2)', $column->type);
        $this->assertTrue($column->nullable);
        $this->assertEquals(0.00, $column->default);
        $this->assertEquals(['admin'], $column->roles);
    }

    public function test_id_attribute_defaults(): void
    {
        $id = new Id;

        $this->assertTrue($id->autoIncrement);
    }

    public function test_id_without_auto_increment(): void
    {
        $id = new Id(autoIncrement: false);

        $this->assertFalse($id->autoIncrement);
    }

    public function test_belongs_to_attribute(): void
    {
        $belongsTo = new BelongsTo(
            relatedEntity: 'Domain\\Users\\Entities\\User',
            foreignKey: 'user_id'
        );

        $this->assertEquals('Domain\\Users\\Entities\\User', $belongsTo->relatedEntity);
        $this->assertEquals('user_id', $belongsTo->foreignKey);
    }

    public function test_belongs_to_defaults(): void
    {
        $belongsTo = new BelongsTo(relatedEntity: 'Domain\\Posts\\Entities\\Post');

        $this->assertEquals('Domain\\Posts\\Entities\\Post', $belongsTo->relatedEntity);
        $this->assertEquals('', $belongsTo->foreignKey);
        $this->assertTrue($belongsTo->nullable);
    }

    public function test_has_many_attribute(): void
    {
        $hasMany = new HasMany(
            relatedEntity: 'Domain\\Comments\\Entities\\Comment',
            foreignKey: 'post_id'
        );

        $this->assertEquals('Domain\\Comments\\Entities\\Comment', $hasMany->relatedEntity);
        $this->assertEquals('post_id', $hasMany->foreignKey);
    }

    public function test_many_to_many_attribute(): void
    {
        $manyToMany = new ManyToMany(
            relatedEntity: 'Domain\\Roles\\Entities\\Role',
            pivotTable: 'user_roles',
            foreignPivotKey: 'user_id',
            relatedPivotKey: 'role_id'
        );

        $this->assertEquals('Domain\\Roles\\Entities\\Role', $manyToMany->relatedEntity);
        $this->assertEquals('user_roles', $manyToMany->pivotTable);
        $this->assertEquals('user_id', $manyToMany->foreignPivotKey);
        $this->assertEquals('role_id', $manyToMany->relatedPivotKey);
    }

    public function test_many_to_many_defaults(): void
    {
        $manyToMany = new ManyToMany(relatedEntity: 'Domain\\Tags\\Entities\\Tag');

        $this->assertEquals('Domain\\Tags\\Entities\\Tag', $manyToMany->relatedEntity);
        $this->assertEquals('', $manyToMany->pivotTable);
        $this->assertEquals('', $manyToMany->foreignPivotKey);
        $this->assertEquals('', $manyToMany->relatedPivotKey);
    }
}
