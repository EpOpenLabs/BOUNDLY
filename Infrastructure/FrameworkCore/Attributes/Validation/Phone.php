<?php

namespace Infrastructure\FrameworkCore\Attributes\Validation;

use Attribute;

/**
 * Validates phone number format with region support.
 *
 * @example
 * ```php
 * #[Phone(region: 'US')]
 * private string $phoneNumber;
 * ```
 *
 * @property string $region ISO 3166-1 alpha-2 country code
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
class Phone
{
    public function __construct(
        public string $region = 'US'
    ) {}
}
