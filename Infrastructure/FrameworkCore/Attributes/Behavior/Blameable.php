<?php

namespace Infrastructure\FrameworkCore\Attributes\Behavior;

use Attribute;

/**
 * Extended audit trail that tracks WHO performed each operation.
 *
 * Similar to Auditable but also tracks who deleted records (when combined
 * with SoftDelete). Essential for GDPR compliance.
 *
 * @example
 * ```php
 * #[Entity(table: 'contracts')]
 * #[Blameable]
 * #[SoftDelete]
 * class Contract extends AggregateRoot { ... }
 * ```
 *
 * Adds columns: created_by, updated_by, deleted_by (VARCHAR)
 *
 * @property string $createdBy Column for creator user ID
 * @property string $updatedBy Column for last modifier user ID
 * @property string $deletedBy Column for deleter user ID
 */
#[Attribute(Attribute::TARGET_CLASS)]
class Blameable
{
    public function __construct(
        public string $createdBy = 'created_by',
        public string $updatedBy = 'updated_by',
        public string $deletedBy = 'deleted_by'
    ) {}
}
