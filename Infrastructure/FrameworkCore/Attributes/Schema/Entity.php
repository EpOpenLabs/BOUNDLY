<?php

namespace Infrastructure\FrameworkCore\Attributes\Schema;

use Attribute;

/**
 * Marks a class as a persistent database entity.
 *
 * This attribute is required for every entity that should be mapped to a database table.
 * It triggers automatic schema migration, route registration, and CRUD operations.
 *
 * @example
 * ```php
 * #[Entity(table: 'users', resource: 'users')]
 * class User extends AggregateRoot { ... }
 * ```
 *
 * @property string $table The database table name
 * @property string|null $resource The API resource name (plural). Defaults to table name
 * @property string|null $morphName Morph map alias for polymorphic relations
 * @property string $connection Database connection to use (default: mysql)
 */
#[Attribute(Attribute::TARGET_CLASS)]
class Entity
{
    public function __construct(
        public string $table,
        public ?string $resource = null,
        public ?string $morphName = null
    ) {}
}
