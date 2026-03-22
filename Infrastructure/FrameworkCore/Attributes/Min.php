<?php

namespace Infrastructure\FrameworkCore\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class Min {
    public function __construct(public int $value) {}
}
