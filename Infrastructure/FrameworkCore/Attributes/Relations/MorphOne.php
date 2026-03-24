<?php

namespace Infrastructure\FrameworkCore\Attributes\Relations;

use Attribute;

/**
 * Defines a Polymorphic one-to-one relationship.
 *
 * Similar to MorphMany but for single related entities.
 *
 * @example
 * ```php
 * #[MorphOne(relatedEntity: Image::class, relation: 'imageable')]
 * private array $avatar;
 * ```
 *
 * @property string $relatedEntity The child entity class name
 * @property string $relation The polymorphic relation name
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
class MorphOne
{
    public function __construct(
        public string $relatedEntity,
        public string $relation
    ) {}
}
