<?php

namespace Infrastructure\FrameworkCore\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
class Action
{
    public function __construct(
        public string $resource,
        public string $method = 'POST'
    ) {}
}
