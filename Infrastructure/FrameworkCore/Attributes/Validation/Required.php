<?php

namespace Infrastructure\FrameworkCore\Attributes\Validation;

use Attribute;

/**
 * Marks a field as required (cannot be null or empty).
 *
 * @example
 * ```php
 * #[Required]
 * #[Column(type: 'string', length: 150)]
 * private string $name;
 * ```
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
class Required {}
