<?php

namespace Infrastructure\FrameworkCore\Attributes\Schema;

use Attribute;

/**
 * Defines composite unique constraints across multiple columns.
 *
 * Use this at the entity level to enforce business rules like
 * "no duplicate active records per tenant".
 *
 * @example
 * ```php
 * #[Entity(table: 'product_variants')]
 * #[UniqueConstraint(columns: ['product_id', 'sku'])]
 * class ProductVariant extends AggregateRoot { ... }
 * ```
 *
 * @property array $columns Array of column names
 * @property string|null $name Custom constraint name
 */
#[Attribute(Attribute::TARGET_CLASS)]
class UniqueConstraint
{
    public function __construct(
        public array $columns,
        public ?string $name = null
    ) {}
}
