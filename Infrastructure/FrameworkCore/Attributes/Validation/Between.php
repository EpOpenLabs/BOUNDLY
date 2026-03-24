<?php

namespace Infrastructure\FrameworkCore\Attributes\Validation;

use Attribute;

/**
 * Validates value is within a range (inclusive).
 *
 * @example
 * ```php
 * #[Between(min: 1, max: 10)]
 * private int $rating;
 *
 * #[Between(min: 0.00, max: 999.99)]
 * private string $price;
 * ```
 *
 * @property int|float $min Minimum value (inclusive)
 * @property int|float $max Maximum value (inclusive)
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
class Between
{
    public function __construct(
        public int|float $min,
        public int|float $max
    ) {}
}
