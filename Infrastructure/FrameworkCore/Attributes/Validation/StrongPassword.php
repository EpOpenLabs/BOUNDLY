<?php

namespace Infrastructure\FrameworkCore\Attributes\Validation;

use Attribute;

/**
 * Shorthand for full password validation.
 *
 * Enforces: 8+ chars, A-Z, a-z, 0-9, special characters.
 *
 * @example
 * ```php
 * #[StrongPassword]
 * private string $password;
 * ```
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
class StrongPassword extends Password
{
    public function __construct()
    {
        parent::__construct(
            minLength: 8,
            requireUppercase: true,
            requireLowercase: true,
            requireNumbers: true,
            requireSpecialChars: true
        );
    }
}
