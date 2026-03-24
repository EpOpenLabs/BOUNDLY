<?php

namespace Infrastructure\FrameworkCore\Attributes\Validation;

use Attribute;

/**
 * Validates hexadecimal color code (#RRGGBB or #RGB).
 *
 * @example
 * ```php
 * #[ColorHex]
 * #[Column(type: 'string', length: 7)]
 * private string $brandColor;
 * ```
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
class ColorHex {}
