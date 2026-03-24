<?php

namespace Infrastructure\FrameworkCore\Attributes\Validation;

use Attribute;

/**
 * Validates file size is within limits.
 *
 * @example
 * ```php
 * #[FileSize(maxMb: 5, minMb: 0.01)]
 * private string $document;
 * ```
 *
 * @property int $maxMb Maximum size in megabytes
 * @property int $minMb Minimum size in megabytes
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
class FileSize
{
    public function __construct(
        public int $maxMb = 10,
        public int $minMb = 0
    ) {}
}
