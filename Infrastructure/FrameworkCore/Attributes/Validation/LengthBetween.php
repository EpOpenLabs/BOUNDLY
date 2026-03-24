<?php

namespace Infrastructure\FrameworkCore\Attributes\Validation;

use Attribute;

/**
 * Validates string length is within a range.
 *
 * @example
 * ```php
 * #[LengthBetween(min: 8, max: 32)]
 * private string $password;
 * ```
 *
 * @property int $min Minimum length
 * @property int $max Maximum length
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
class LengthBetween
{
    public function __construct(
        public int $min,
        public int $max
    ) {}
}
