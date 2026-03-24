<?php

namespace Infrastructure\FrameworkCore\Attributes\Validation;

use Attribute;

/**
 * Validates valid timezone identifier.
 *
 * @example
 * ```php
 * #[Timezone]
 * #[Column(type: 'string', length: 50)]
 * private string $userTimezone;
 * ```
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
class Timezone {}
