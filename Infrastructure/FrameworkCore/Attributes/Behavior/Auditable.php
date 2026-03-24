<?php

namespace Infrastructure\FrameworkCore\Attributes\Behavior;

use Attribute;

/**
 * Automates traceability by tracking WHO created and modified each record.
 *
 * Adds created_by and updated_by columns that are automatically populated
 * from the X-User-ID request header (or 'System' for CLI operations).
 *
 * @example
 * ```php
 * #[Entity(table: 'products')]
 * #[Auditable]
 * class Product extends AggregateRoot { ... }
 * ```
 *
 * Adds columns: created_by (VARCHAR), updated_by (VARCHAR)
 */
#[Attribute(Attribute::TARGET_CLASS)]
class Auditable {}
