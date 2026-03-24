<?php

namespace Infrastructure\FrameworkCore\Attributes\Validation;

use Attribute;

/**
 * Validates RFC 5322 compliant email format.
 *
 * @example
 * ```php
 * #[Email]
 * #[Column(type: 'string', length: 255)]
 * private string $email;
 * ```
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
class Email {}
