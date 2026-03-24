<?php

namespace Infrastructure\FrameworkCore\Attributes\Validation;

use Attribute;

/**
 * Validates UUID format.
 *
 * @example
 * ```php
 * #[Uuid(version: 4)]
 * #[Column(type: 'string', length: 36)]
 * private string $correlationId;
 * ```
 *
 * @property int $version UUID version (1, 4, or 5)
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
class Uuid
{
    public function __construct(
        public int $version = 4
    ) {}
}
