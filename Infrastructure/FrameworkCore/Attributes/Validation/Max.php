<?php

namespace Infrastructure\FrameworkCore\Attributes\Validation;

use Attribute;

/**
 * Validates maximum numeric or string value/length.
 *
 * @example
 * ```php
 * #[Max(100)] // For numbers: must be <= 100
 * private int $percentage;
 *
 * #[Max(50)] // For strings: max 50 characters
 * private string $title;
 * ```
 *
 * @property int|float $value Maximum value
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
class Max
{
    public function __construct(public int|float $value) {}
}
