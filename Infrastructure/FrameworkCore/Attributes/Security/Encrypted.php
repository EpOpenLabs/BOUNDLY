<?php

namespace Infrastructure\FrameworkCore\Attributes\Security;

use Attribute;

/**
 * Encrypts the property value before storing in the database.
 *
 * Uses AES-256-CBC encryption by default. Values are automatically decrypted
 * when retrieved from the database. Requires APP_KEY in environment.
 *
 * @example
 * ```php
 * #[Encrypted]
 * #[Column(type: 'text')]
 * private string $apiKey;
 * ```
 *
 * Value is encrypted on INSERT/UPDATE and decrypted on SELECT.
 *
 * @property string $algorithm Encryption algorithm (default: AES-256-CBC)
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
class Encrypted
{
    public function __construct(
        public string $algorithm = 'AES-256-CBC'
    ) {}
}
