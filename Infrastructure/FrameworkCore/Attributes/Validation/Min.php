<?php

namespace Infrastructure\FrameworkCore\Attributes\Validation;

use Attribute;

/**
 * Validates minimum numeric or string value/length.
 *
 * @example
 * ```php
 * #[Min(0)] // For numbers: must be >= 0
 * private int $quantity;
 *
 * #[Min(3)] // For strings: must be at least 3 chars
 * private string $name;
 * ```
 *
 * @property int|float $value Minimum value
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
class Min
{
    public function __construct(public int|float $value) {}
}
