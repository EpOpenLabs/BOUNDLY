<?php

namespace Infrastructure\FrameworkCore\Attributes\Schema;

use Attribute;

/**
 * Defines a database column with its properties.
 *
 * This attribute maps a class property to a database column. BOUNDLY generates
 * the DDL automatically during core:migrate.
 *
 * Supported types: string, text, integer, bigint, boolean, decimal, date, datetime,
 *                  timestamp, json, uuid
 *
 * @example
 * ```php
 * #[Column(type: 'string', length: 150, nullable: true)]
 * private string $nickname;
 *
 * #[Column(type: 'decimal(10,2)', default: 0.00)]
 * private string $price;
 * ```
 *
 * @property string $type Column type (default: 'string')
 * @property int|null $length Max length for string types (default: 255)
 * @property bool $nullable Allow NULL values (default: false)
 * @property mixed $default Default value
 * @property bool $unique Add unique index (default: false)
 * @property array $roles Role-based visibility control
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
class Column
{
    public function __construct(
        public string $type = 'string',
        public ?int $length = null,
        public bool $nullable = false,
        public mixed $default = null,
        public array $roles = []
    ) {}
}
