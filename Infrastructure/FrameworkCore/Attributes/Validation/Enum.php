<?php

namespace Infrastructure\FrameworkCore\Attributes\Validation;

use Attribute;

/**
 * Validates the value matches one of the PHP Enum cases.
 *
 * @example
 * ```php
 * #[Enum(class: OrderStatus::class)]
 * private string $status;
 * ```
 *
 * @property string $class PHP Enum class name
 * @property bool $strict Use strict type comparison
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
class Enum
{
    public function __construct(
        public string $class,
        public bool $strict = true
    ) {}
}
