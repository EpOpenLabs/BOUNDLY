<?php

namespace Infrastructure\FrameworkCore\Attributes\Validation;

use Attribute;

/**
 * Validates the field is different from another field's value.
 *
 * @example
 * ```php
 * #[DifferentFrom(field: 'currentPassword')]
 * private string $newPassword;
 * ```
 *
 * @property string $field Field name to differ from
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
class DifferentFrom
{
    public function __construct(public string $field) {}
}
