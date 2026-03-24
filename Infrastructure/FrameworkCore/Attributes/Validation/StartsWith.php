<?php

namespace Infrastructure\FrameworkCore\Attributes\Validation;

use Attribute;

/**
 * Validates the string starts with a specific value.
 *
 * @example
 * ```php
 * #[StartsWith(value: 'https://')]
 * private string $callbackUrl;
 * ```
 *
 * @property string $value Required prefix
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
class StartsWith
{
    public function __construct(public string $value) {}
}
