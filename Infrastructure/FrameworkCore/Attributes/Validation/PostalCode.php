<?php

namespace Infrastructure\FrameworkCore\Attributes\Validation;

use Attribute;

/**
 * Validates postal code format by country.
 *
 * @example
 * ```php
 * #[PostalCode(country: 'US')]
 * private string $zipCode;
 * ```
 *
 * @property string $country ISO 3166-1 alpha-2 country code
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
class PostalCode
{
    public function __construct(
        public string $country = 'US'
    ) {}
}
