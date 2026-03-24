<?php

namespace Infrastructure\FrameworkCore\Attributes\Validation;

use Attribute;

/**
 * Validates the field matches another field's value.
 *
 * @example
 * ```php
 * #[SameAs(field: 'recoveryEmail')]
 * private string $confirmRecoveryEmail;
 * ```
 *
 * @property string $field Field name to match
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
class SameAs
{
    public function __construct(public string $field) {}
}
