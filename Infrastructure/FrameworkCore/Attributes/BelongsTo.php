<?php
namespace Infrastructure\FrameworkCore\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class BelongsTo
{
    public function __construct(
        public string $relatedEntity,
        public string $foreignKey = '',
        public bool $nullable = true
    ) {}
}
