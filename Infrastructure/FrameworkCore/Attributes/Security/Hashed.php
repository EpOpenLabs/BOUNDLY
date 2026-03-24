<?php

namespace Infrastructure\FrameworkCore\Attributes\Security;

use Attribute;

/**
 * Automatically hashes the value before storage.
 *
 * Ideal for passwords and sensitive strings that should never be retrievable.
 * Uses bcrypt by default. Hashing is one-way and cannot be decrypted.
 *
 * @example
 * ```php
 * #[Hashed(algorithm: 'bcrypt', options: ['rounds' => 12])]
 * #[Column(type: 'string')]
 * private string $password;
 * ```
 *
 * Automatically hashes on INSERT and UPDATE (if changed).
 * Use Hash::check() to verify values.
 *
 * @property string $algorithm Hashing algorithm (bcrypt, argon2i, argon2id)
 * @property array $options Algorithm-specific options
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
class Hashed
{
    public function __construct(
        public string $algorithm = 'bcrypt',
        public array $options = []
    ) {}
}
