<?php

namespace Infrastructure\FrameworkCore\Attributes\Relations;

use Attribute;

/**
 * Declares the inverse side of a one-to-many relationship.
 *
 * No schema changes needed - handled by the inverse #[BelongsTo] side.
 * Use this to enable eager loading of child entities.
 *
 * @example
 * ```php
 * #[HasMany(relatedEntity: Post::class)]
 * private array $posts;
 * ```
 *
 * @property string $relatedEntity The child entity class name
 * @property string $foreignKey The FK column on the child entity
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
class HasMany
{
    public function __construct(
        public string $relatedEntity,
        public string $foreignKey = ''
    ) {}
}
