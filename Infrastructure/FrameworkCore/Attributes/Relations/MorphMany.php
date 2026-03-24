<?php

namespace Infrastructure\FrameworkCore\Attributes\Relations;

use Attribute;

/**
 * Defines a Polymorphic one-to-many relationship.
 *
 * The entity becomes a potential "parent" of many other polymorphic entities.
 * The child entity must have #[MorphTo] to complete the relationship.
 *
 * @example
 * ```php
 * // Parent side
 * #[MorphMany(relatedEntity: Comment::class, relation: 'commentable')]
 * private array $comments;
 * ```
 *
 * @property string $relatedEntity The child entity class name
 * @property string $relation The polymorphic relation name
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
class MorphMany
{
    public function __construct(
        public string $relatedEntity,
        public string $relation
    ) {}
}
