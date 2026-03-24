<?php

namespace Infrastructure\FrameworkCore\Attributes\Validation;

use Attribute;

/**
 * Validates that the value is valid JSON.
 *
 * @example
 * ```php
 * #[Json]
 * #[Column(type: 'json')]
 * private string $metadata;
 * ```
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
class Json {}
