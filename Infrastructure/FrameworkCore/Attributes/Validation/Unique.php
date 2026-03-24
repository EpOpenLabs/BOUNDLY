<?php

namespace Infrastructure\FrameworkCore\Attributes\Validation;

use Attribute;

/**
 * Validates the value is unique in the database table.
 *
 * @example
 * ```php
 * #[Unique]
 * private string $username;
 *
 * #[Unique(column: 'email', except: 'current_id')]
 * private string $email;
 * ```
 *
 * @property string|null $column Column name to check (defaults to property name)
 * @property string|null $except Ignore this ID when checking (for updates)
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
class Unique
{
    public function __construct(
        public ?string $column = null,
        public ?string $except = null
    ) {}
}
