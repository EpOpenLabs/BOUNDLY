<?php

namespace Infrastructure\FrameworkCore\Attributes\Schema;

use Attribute;

/**
 * Defines a foreign key constraint with cascade behavior.
 *
 * Use this on properties with #[BelongsTo] to configure the FK constraint.
 * BOUNDLY automatically creates the constraint during core:migrate.
 *
 * @example
 * ```php
 * #[ForeignKey(entity: User::class, onDelete: 'CASCADE')]
 * #[BelongsTo(relatedEntity: User::class)]
 * private array $author;
 * ```
 *
 * @property string $entity Related entity class name
 * @property string|null $column FK column name (auto-generated if null)
 * @property string $onDelete Action on delete (CASCADE, SET NULL, RESTRICT, NO ACTION)
 * @property string $onUpdate Action on update (CASCADE, SET NULL, RESTRICT, NO ACTION)
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
class ForeignKey
{
    public function __construct(
        public string $entity,
        public ?string $column = null,
        public string $onDelete = 'RESTRICT',
        public string $onUpdate = 'CASCADE'
    ) {}
}
