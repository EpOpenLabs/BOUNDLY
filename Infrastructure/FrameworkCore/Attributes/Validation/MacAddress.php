<?php

namespace Infrastructure\FrameworkCore\Attributes\Validation;

use Attribute;

/**
 * Validates MAC address format.
 *
 * @example
 * ```php
 * #[MacAddress]
 * #[Column(type: 'string', length: 17)]
 * private string $deviceMac;
 * ```
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
class MacAddress {}
