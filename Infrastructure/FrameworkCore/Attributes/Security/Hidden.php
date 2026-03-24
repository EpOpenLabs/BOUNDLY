<?php

namespace Infrastructure\FrameworkCore\Attributes\Security;

use Attribute;

/**
 * Excludes a property from all API responses.
 *
 * Useful for sensitive data that should never be exposed to clients.
 * The property is still stored in the database normally.
 *
 * @example
 * ```php
 * #[Hidden]
 * #[Column(type: 'string')]
 * private string $password;
 * ```
 *
 * The password will NOT appear in JSON responses but is stored in DB.
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
class Hidden {}
