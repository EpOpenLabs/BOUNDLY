<?php

namespace Infrastructure\FrameworkCore\Attributes\Validation;

use Attribute;

/**
 * Validates password strength with configurable rules.
 *
 * @example
 * ```php
 * #[Password(minLength: 12, requireSpecialChars: true)]
 * private string $password;
 * ```
 *
 * @property int $minLength Minimum length
 * @property bool $requireUppercase Require uppercase letter
 * @property bool $requireLowercase Require lowercase letter
 * @property bool $requireNumbers Require number
 * @property bool $requireSpecialChars Require special character
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
class Password
{
    public function __construct(
        public int $minLength = 8,
        public bool $requireUppercase = true,
        public bool $requireLowercase = true,
        public bool $requireNumbers = true,
        public bool $requireSpecialChars = true
    ) {}
}
