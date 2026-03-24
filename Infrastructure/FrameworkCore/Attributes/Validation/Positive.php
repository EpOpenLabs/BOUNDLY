<?php

namespace Infrastructure\FrameworkCore\Attributes\Validation;

use Attribute;

/**
 * Validates the value is strictly greater than zero.
 *
 * @example
 * ```php
 * #[Positive]
 * private int $quantity;
 * ```
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
class Positive {}
