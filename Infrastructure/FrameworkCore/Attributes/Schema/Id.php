<?php

namespace Infrastructure\FrameworkCore\Attributes\Schema;

use Attribute;

/**
 * Marks a property as the primary key.
 *
 * By default, creates an auto-incrementing BIGINT primary key.
 * Use this on your ID property in every entity.
 *
 * @example
 * ```php
 * #[Id]
 * private int $id;
 * ```
 *
 * @property bool $autoIncrement Auto-increment the ID (default: true)
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
class Id
{
    public function __construct(
        public bool $autoIncrement = true
    ) {}
}
