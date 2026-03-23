<?php

namespace Infrastructure\FrameworkCore\Attributes;

use Attribute;

/**
 * Defines a Polymorphic 1-to-1 relationship.
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
class MorphOne
{
    public function __construct(
        public string $relatedEntity,
        public string $relation
    ) {}
}
