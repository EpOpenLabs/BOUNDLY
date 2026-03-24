<?php

namespace Infrastructure\FrameworkCore\Attributes\Validation;

use Attribute;

/**
 * Validates the value is strictly less than zero.
 *
 * @example
 * ```php
 * #[Negative]
 * private int $temperatureCelsius;
 * ```
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
class Negative {}
