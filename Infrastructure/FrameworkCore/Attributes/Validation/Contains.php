<?php

namespace Infrastructure\FrameworkCore\Attributes\Validation;

use Attribute;

/**
 * Validates the string contains a specific substring.
 *
 * @example
 * ```php
 * #[Contains(value: 'promo')]
 * private string $couponCode;
 * ```
 *
 * @property string $value Required substring
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
class Contains
{
    public function __construct(public string $value) {}
}
