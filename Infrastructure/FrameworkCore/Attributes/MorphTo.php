<?php

namespace Infrastructure\FrameworkCore\Attributes;

use Attribute;

/**
 * Defines a Polymorphic M-to-1 relationship.
 * The entity becomes the "child" of any other entity that implements MorphMany.
 * It will automatically generate both {name}_id and {name}_type columns.
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
class MorphTo
{
    public function __construct(
        public ?string $name = null
    ) {}
}
