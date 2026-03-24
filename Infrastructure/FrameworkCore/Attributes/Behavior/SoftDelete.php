<?php

namespace Infrastructure\FrameworkCore\Attributes\Behavior;

use Attribute;

/**
 * Enables logical (soft) deletion of records.
 *
 * Records are never physically removed. Instead, deleted_at is set to current
 * timestamp. All queries automatically filter deleted records (WHERE deleted_at IS NULL).
 *
 * @example
 * ```php
 * #[Entity(table: 'orders')]
 * #[SoftDelete]
 * class Order extends AggregateRoot { ... }
 * ```
 *
 * Adds column: deleted_at (TIMESTAMP, NULL = active)
 */
#[Attribute(Attribute::TARGET_CLASS)]
class SoftDelete {}
