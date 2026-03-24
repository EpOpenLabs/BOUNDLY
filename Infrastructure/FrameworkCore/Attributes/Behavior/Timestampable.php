<?php

namespace Infrastructure\FrameworkCore\Attributes\Behavior;

use Attribute;

/**
 * Automatically manages created_at and updated_at timestamps.
 *
 * Sets created_at on INSERT and updated_at on every UPDATE automatically.
 *
 * @example
 * ```php
 * #[Entity(table: 'articles')]
 * #[Timestampable(createdAt: 'published_at', updatedAt: 'last_modified_at')]
 * class Article extends AggregateRoot { ... }
 * ```
 *
 * @property string $createdAt Column name for creation time
 * @property string $updatedAt Column name for last update time
 */
#[Attribute(Attribute::TARGET_CLASS)]
class Timestampable
{
    public function __construct(
        public string $createdAt = 'created_at',
        public string $updatedAt = 'updated_at'
    ) {}
}
