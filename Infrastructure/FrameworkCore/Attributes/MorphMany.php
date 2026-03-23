<?php

namespace Infrastructure\FrameworkCore\Attributes;

use Attribute;

/**
 * Defines a Polymorphic 1-to-M relationship.
 * The entity becomes a potential "parent" of many other polymorphic entities.
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
class MorphMany
{
    public function __construct(
        public string $relatedEntity,
        public string $relation
    ) {}
}
