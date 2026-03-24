<?php

namespace Tests\FrameworkCore\Unit;

use Infrastructure\FrameworkCore\Registry\EntityRegistry;
use PHPUnit\Framework\TestCase;

class EntityRegistryTest extends TestCase
{
    private EntityRegistry $registry;

    protected function setUp(): void
    {
        parent::setUp();
        $this->registry = new EntityRegistry;
    }

    public function test_register_class_ignores_non_entity_classes(): void
    {
        $this->registry->registerClass('Some\\Non\\Entity\\Class');

        $entities = $this->registry->getAllEntities();
        $this->assertEmpty($entities);
    }

    public function test_get_entity_config_returns_null_for_undefined(): void
    {
        $config = $this->registry->getEntityConfig('undefined');

        $this->assertNull($config);
    }

    public function test_get_class_by_morph_returns_input_when_not_found(): void
    {
        $result = $this->registry->getClassByMorph('unknown_alias');

        $this->assertEquals('unknown_alias', $result);
    }

    public function test_get_morph_by_class_returns_input_when_not_found(): void
    {
        $result = $this->registry->getMorphByClass('Unknown\\Class\\Name');

        $this->assertEquals('Unknown\\Class\\Name', $result);
    }

    public function test_find_entity_by_table_returns_null_when_not_found(): void
    {
        $result = $this->registry->findEntityByTable('non_existent_table');

        $this->assertNull($result);
    }

    public function test_find_entity_by_class_returns_null_when_not_found(): void
    {
        $result = $this->registry->findEntityByClass('Non\\Existent\\Class');

        $this->assertNull($result);
    }

    public function test_hydrate_from_cache_sets_entities(): void
    {
        $cachedData = [
            'users' => [
                'resource' => 'users',
                'class' => 'Domain\\Users\\Entities\\User',
                'table' => 'users',
                'primaryKey' => 'id',
                'columns' => [],
                'hasMany' => [],
                'belongsTo' => [],
                'hasOne' => [],
                'manyToMany' => [],
                'morphTo' => [],
                'morphMany' => [],
                'morphOne' => [],
                'hidden' => [],
                'tenantAware' => false,
                'tenantColumn' => null,
                'auditable' => false,
                'softDelete' => false,
            ],
        ];

        $this->registry->hydrateFromCache($cachedData);
        $config = $this->registry->getEntityConfig('users');

        $this->assertNotNull($config);
        $this->assertEquals('users', $config['resource']);
        $this->assertEquals('Domain\\Users\\Entities\\User', $config['class']);
        $this->assertEquals('users', $config['table']);
    }

    public function test_get_all_entities_returns_array(): void
    {
        $entities = $this->registry->getAllEntities();

        $this->assertIsArray($entities);
    }

    public function test_register_class_returns_early_for_nonexistent_class(): void
    {
        $this->registry->registerClass('Non\\Existent\\Class\\That\\Does\\Not\\Exist');

        $this->assertEmpty($this->registry->getAllEntities());
    }

    public function test_get_morph_by_class_returns_full_class_name_when_not_in_morph_map(): void
    {
        $cachedData = [
            'posts' => [
                'resource' => 'posts',
                'class' => 'Domain\\Posts\\Entities\\Post',
                'table' => 'posts',
                'primaryKey' => 'id',
                'columns' => [],
                'hasMany' => [],
                'belongsTo' => [],
                'hasOne' => [],
                'manyToMany' => [],
                'morphTo' => [],
                'morphMany' => [],
                'morphOne' => [],
                'hidden' => [],
                'tenantAware' => false,
                'tenantColumn' => null,
                'auditable' => false,
                'softDelete' => false,
            ],
        ];

        $this->registry->hydrateFromCache($cachedData);
        $result = $this->registry->getMorphByClass('Domain\\Posts\\Entities\\Post');

        $this->assertEquals('Domain\\Posts\\Entities\\Post', $result);
    }
}
