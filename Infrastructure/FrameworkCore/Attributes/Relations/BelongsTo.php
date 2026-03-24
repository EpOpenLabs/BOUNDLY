<?php

namespace Infrastructure\FrameworkCore\Attributes\Relations;

use Attribute;

/**
 * Declares that this entity belongs to another (N→1 relationship).
 *
 * Automatically creates the foreign key column on this entity.
 * This is the "owning" side of one-to-many relationships.
 *
 * @example
 * ```php
 * #[BelongsTo(relatedEntity: User::class)]
 * private array $author;
 * ```
 *
 * @property string $relatedEntity The parent entity class name
 * @property string $foreignKey FK column name (auto-generated if empty)
 * @property bool $nullable Allow NULL when no parent exists (default: true)
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
class BelongsTo
{
    public function __construct(
        public string $relatedEntity,
        public string $foreignKey = '',
        public bool $nullable = true
    ) {}
}
