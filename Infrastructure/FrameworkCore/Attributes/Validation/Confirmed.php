<?php

namespace Infrastructure\FrameworkCore\Attributes\Validation;

use Attribute;

/**
 * Validates that a matching confirmation field exists.
 *
 * Automatically checks for a field named {property}_confirmation.
 *
 * @example
 * ```php
 * #[Confirmed]
 * private string $password;
 * // Requires password_confirmation in request
 * ```
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
class Confirmed {}
