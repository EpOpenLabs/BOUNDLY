<?php

namespace Infrastructure\FrameworkCore\Attributes\Validation;

use Attribute;

/**
 * Validates only alphabetic characters (a-z, A-Z).
 *
 * @example
 * ```php
 * #[Alpha]
 * private string $firstName;
 * ```
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
class Alpha {}
