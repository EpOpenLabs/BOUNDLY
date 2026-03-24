<?php

namespace Infrastructure\FrameworkCore\Attributes\Validation;

use Attribute;

/**
 * Validates that the field value is a valid URL.
 *
 * Supports http, https, and ftp protocols.
 *
 * @example
 * ```php
 * #[Url]
 * #[Column(type: 'string', length: 500)]
 * private string $website;
 * ```
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
class Url {}
