<?php

namespace Infrastructure\FrameworkCore\Attributes\Validation;

use Attribute;

/**
 * Validates against a custom regular expression.
 *
 * @example
 * ```php
 * #[Pattern(regex: '^[A-Z]{3}[0-9]{4}$')]
 * private string $productCode;
 * ```
 *
 * @property string $regex Regular expression pattern
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
class Pattern
{
    public function __construct(public string $regex) {}
}
