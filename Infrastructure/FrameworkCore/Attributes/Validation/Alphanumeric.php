<?php

namespace Infrastructure\FrameworkCore\Attributes\Validation;

use Attribute;

/**
 * Validates only alphanumeric characters (a-z, A-Z, 0-9).
 *
 * @example
 * ```php
 * #[Alphanumeric]
 * private string $username;
 * ```
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
class Alphanumeric {}
