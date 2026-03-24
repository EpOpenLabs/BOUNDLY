<?php

namespace Infrastructure\FrameworkCore\Attributes\Validation;

use Attribute;

/**
 * Validates latitude/longitude coordinates.
 *
 * @example
 * ```php
 * #[Coordinates]
 * private string $location;
 * ```
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
class Coordinates {}
