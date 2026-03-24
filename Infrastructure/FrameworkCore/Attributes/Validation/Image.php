<?php

namespace Infrastructure\FrameworkCore\Attributes\Validation;

use Attribute;

/**
 * Validates file is a valid image with allowed formats and size.
 *
 * @example
 * ```php
 * #[Image(mimes: ['jpg', 'png', 'webp'], maxSizeKb: 2048)]
 * private string $avatar;
 * ```
 *
 * @property array $mimes Allowed image extensions
 * @property int $maxSizeKb Maximum file size in KB (default: 5120 = 5MB)
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
class Image
{
    public function __construct(
        public array $mimes = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg', 'bmp'],
        public int $maxSizeKb = 5120
    ) {}
}
