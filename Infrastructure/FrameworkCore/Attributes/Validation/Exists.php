<?php

namespace Infrastructure\FrameworkCore\Attributes\Validation;

use Attribute;

/**
 * Validates the value exists in another entity's table.
 *
 * @example
 * ```php
 * #[Exists(entity: Category::class)]
 * private int $categoryId;
 * ```
 *
 * @property string $entity Entity class name to check against
 * @property string|null $column Column name (defaults to entity's primary key)
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
class Exists
{
    public function __construct(
        public string $entity,
        public ?string $column = null
    ) {}
}
