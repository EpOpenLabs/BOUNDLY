<?php

namespace Infrastructure\FrameworkCore\Attributes\Validation;

use Attribute;

/**
 * Validates only numeric characters (0-9).
 *
 * @example
 * ```php
 * #[Numeric]
 * private string $zipCode;
 * ```
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
class Numeric {}
