<?php

namespace Infrastructure\FrameworkCore\Attributes\Relations;

use Attribute;

/**
 * Declares a one-to-one relationship where this entity has one related entity.
 *
 * No schema changes needed - handled by the inverse #[BelongsTo] side.
 *
 * @example
 * ```php
 * #[HasOne(relatedEntity: Profile::class)]
 * private array $profile;
 * ```
 *
 * @property string $relatedEntity The related entity class name
 * @property string $foreignKey The FK column on the related entity
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
class HasOne
{
    public function __construct(
        public string $relatedEntity,
        public string $foreignKey = ''
    ) {}
}
