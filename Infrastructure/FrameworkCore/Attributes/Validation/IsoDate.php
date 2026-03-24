<?php

namespace Infrastructure\FrameworkCore\Attributes\Validation;

use Attribute;

/**
 * Validates ISO 8601 date format.
 *
 * @example
 * ```php
 * #[IsoDate]
 * #[Column(type: 'datetime')]
 * private string $eventDate;
 * ```
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
class IsoDate {}
