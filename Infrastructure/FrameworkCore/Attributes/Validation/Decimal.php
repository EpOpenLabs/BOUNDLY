<?php

namespace Infrastructure\FrameworkCore\Attributes\Validation;

use Attribute;

/**
 * Validates decimal numbers with specific precision.
 *
 * @example
 * ```php
 * #[Decimal(decimals: 4)]
 * private string $exchangeRate;
 * ```
 *
 * @property int $decimals Number of decimal places (default: 2)
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
class Decimal
{
    public function __construct(
        public int $decimals = 2
    ) {}
}
