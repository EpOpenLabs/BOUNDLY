<?php

namespace Infrastructure\FrameworkCore\Attributes\Validation;

use Attribute;

/**
 * Validates the value is a whole number (no decimals).
 *
 * @example
 * ```php
 * #[Integer]
 * private string $userId;
 * ```
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
class Integer {}
