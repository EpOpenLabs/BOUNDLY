<?php
namespace Infrastructure\FrameworkCore\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class HasMany
{
    public function __construct(
        public string $relatedEntity,
        public string $foreignKey = ''
    ) {}
}
