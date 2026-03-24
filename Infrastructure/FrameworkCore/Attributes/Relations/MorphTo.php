<?php

namespace Infrastructure\FrameworkCore\Attributes\Relations;

use Attribute;

/**
 * Defines the child side of a Polymorphic relationship.
 *
 * Automatically generates {name}_id and {name}_type columns.
 * This allows the entity to belong to any other entity.
 *
 * @example
 * ```php
 * #[MorphTo(name: 'commentable')]
 * private array $commentable;
 * ```
 *
 * @property string|null $name Morph name (defaults to property name)
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
class MorphTo
{
    public function __construct(
        public ?string $name = null
    ) {}
}
