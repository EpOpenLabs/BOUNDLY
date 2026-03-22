<?php

namespace Infrastructure\FrameworkCore\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class Id
{
    public function __construct(
        public bool $autoIncrement = true
    ) {}
}
