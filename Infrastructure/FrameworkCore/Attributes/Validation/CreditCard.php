<?php

namespace Infrastructure\FrameworkCore\Attributes\Validation;

use Attribute;

/**
 * Validates credit card numbers using the Luhn algorithm.
 *
 * @example
 * ```php
 * #[CreditCard]
 * private string $cardNumber;
 * ```
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
class CreditCard {}
