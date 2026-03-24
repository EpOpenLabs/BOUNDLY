<?php

namespace Infrastructure\FrameworkCore\Attributes\Validation;

use Attribute;

/**
 * Validates minimum string length.
 *
 * @example
 * ```php
 * #[MinLength(3)]
 * private string $username;
 * ```
 *
 * @property int $value Minimum length
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
class MinLength
{
    public function __construct(public int $value) {}
}
