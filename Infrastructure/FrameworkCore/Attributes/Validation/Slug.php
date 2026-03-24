<?php

namespace Infrastructure\FrameworkCore\Attributes\Validation;

use Attribute;

/**
 * Validates URL-friendly slug format (lowercase, alphanumeric, hyphens).
 *
 * @example
 * ```php
 * #[Slug(maxLength: 100)]
 * #[Column(type: 'string', length: 200)]
 * private string $urlSlug;
 * ```
 *
 * @property int $maxLength Maximum slug length (default: 255)
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
class Slug
{
    public function __construct(
        public int $maxLength = 255
    ) {}
}
