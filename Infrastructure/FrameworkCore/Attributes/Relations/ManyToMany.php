<?php

namespace Infrastructure\FrameworkCore\Attributes\Relations;

use Attribute;

/**
 * Creates a many-to-many relationship with an automatic pivot table.
 *
 * Generates a pivot table with foreign keys to both entities.
 * Use in payload with array of IDs for automatic sync.
 *
 * @example
 * ```php
 * #[ManyToMany(relatedEntity: Role::class)]
 * private array $roles;
 * ```
 *
 * Generates pivot table: role_user (or custom name)
 *
 * @property string $relatedEntity The related entity class name
 * @property string $pivotTable Pivot table name (auto-generated if empty)
 * @property string $foreignPivotKey FK on pivot pointing to this entity
 * @property string $relatedPivotKey FK on pivot pointing to related entity
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
class ManyToMany
{
    public function __construct(
        public string $relatedEntity,
        public string $pivotTable = '',
        public string $foreignPivotKey = '',
        public string $relatedPivotKey = ''
    ) {}
}
