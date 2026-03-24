<?php

namespace Infrastructure\FrameworkCore\Attributes\Validation;

use Attribute;

/**
 * Validates maximum string length.
 *
 * @example
 * ```php
 * #[MaxLength(50)]
 * private string $title;
 * ```
 *
 * @property int $value Maximum length
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
class MaxLength
{
    public function __construct(public int $value) {}
}
