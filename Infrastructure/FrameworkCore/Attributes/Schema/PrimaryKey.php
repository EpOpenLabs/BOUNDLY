<?php

namespace Infrastructure\FrameworkCore\Attributes\Schema;

use Attribute;

/**
 * Extended primary key configuration.
 *
 * Use this when you need custom column types or non-auto-incrementing keys.
 * For example, when using UUID primary keys.
 *
 * @example
 * ```php
 * // UUID Primary Key
 * #[PrimaryKey(autoIncrement: false, type: 'uuid')]
 * private string $id;
 * ```
 *
 * @property bool $autoIncrement Auto-increment the ID (default: true)
 * @property string $type Column type (default: 'bigint')
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
class PrimaryKey extends Id
{
    public function __construct(
        public bool $autoIncrement = true,
        public string $type = 'bigint'
    ) {}
}
