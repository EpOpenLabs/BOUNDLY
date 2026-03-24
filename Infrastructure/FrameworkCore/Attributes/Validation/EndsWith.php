<?php

namespace Infrastructure\FrameworkCore\Attributes\Validation;

use Attribute;

/**
 * Validates the string ends with a specific value.
 *
 * @example
 * ```php
 * #[EndsWith(value: '@company.com')]
 * private string $email;
 * ```
 *
 * @property string $value Required suffix
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
class EndsWith
{
    public function __construct(public string $value) {}
}
