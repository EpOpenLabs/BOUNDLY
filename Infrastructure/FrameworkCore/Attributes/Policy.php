<?php

namespace Infrastructure\FrameworkCore\Attributes;

use Attribute;

/**
 * Maps a Domain Entity to a Laravel Policy for fine-grained authorization.
 *
 * Example:
 * #[Policy(PostPolicy::class)]
 */
#[Attribute(Attribute::TARGET_CLASS)]
class Policy
{
    /**
     * @param string|null $class The Policy class name. If null, it will look for EntityPolicy.
     * @param array $methods Optional: specify policy methods to use for certain verbs.
     */
    public function __construct(
        public ?string $class = null,
        public array $methods = []
    ) {}
}
