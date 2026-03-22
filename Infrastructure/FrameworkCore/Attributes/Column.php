<?php

namespace Infrastructure\FrameworkCore\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class Column
{
    public function __construct(
        public string $type = 'string',
        public ?int $length = null,
        public bool $nullable = false,
        public mixed $default = null
    ) {}
}
