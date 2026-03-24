<?php

namespace Infrastructure\FrameworkCore\Attributes\Validation;

use Attribute;

/**
 * Validates file has one of the allowed extensions.
 *
 * @example
 * ```php
 * #[Mimes(types: ['pdf', 'doc', 'docx'])]
 * private string $contract;
 * ```
 *
 * @property array $types Allowed file extensions
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
class Mimes
{
    public function __construct(public array $types) {}
}
