<?php

namespace Infrastructure\FrameworkCore\Attributes\Schema;

use Attribute;

/**
 * Creates database indexes for query performance optimization.
 *
 * Use this attribute to add single or composite indexes on columns.
 * Indexes significantly speed up read queries on large tables.
 *
 * @example
 * ```php
 * // Single column index
 * #[Index]
 * #[Column(type: 'string', length: 150)]
 * private string $email;
 *
 * // Composite index
 * #[Index(columns: ['last_name', 'first_name'])]
 * private array $name;
 *
 * // Unique index
 * #[Index(unique: true)]
 * #[Column(type: 'string', length: 100)]
 * private string $slug;
 * ```
 *
 * @property array|null $columns Column(s) to index (null = property name)
 * @property string|null $name Custom index name
 * @property bool $unique Create unique index (default: false)
 */
#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_PROPERTY)]
class Index
{
    public function __construct(
        public ?array $columns = null,
        public ?string $name = null,
        public bool $unique = false
    ) {}
}
