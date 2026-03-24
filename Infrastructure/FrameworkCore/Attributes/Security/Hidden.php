<?php

namespace Infrastructure\FrameworkCore\Attributes\Security;

use Attribute;

/**
 * Excludes a property from all API responses.
 *
 * Useful for sensitive data that should never be exposed to clients.
 * The property is still stored in the database normally.
 *
 * @example Property-level usage:
 * ```php
 * #[Hidden]
 * #[Column(type: 'string')]
 * private string $password;
 * ```
 * @example Class-level usage:
 * ```php
 * #[Hidden(fields: ['password', 'api_token'])]
 * class User {}
 * ```
 *
 * The password will NOT appear in JSON responses but is stored in DB.
 */
#[Attribute(Attribute::TARGET_PROPERTY | Attribute::TARGET_CLASS)]
class Hidden
{
    public function __construct(
        public array $fields = []
    ) {}
}
