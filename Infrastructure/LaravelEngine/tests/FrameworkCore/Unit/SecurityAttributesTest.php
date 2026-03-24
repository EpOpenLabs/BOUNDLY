<?php

namespace Tests\FrameworkCore\Unit;

use Infrastructure\FrameworkCore\Attributes\Security\Encrypted;
use Infrastructure\FrameworkCore\Attributes\Security\Hashed;
use Infrastructure\FrameworkCore\Attributes\Security\Hidden;
use PHPUnit\Framework\TestCase;

class SecurityAttributesTest extends TestCase
{
    public function test_hidden_attribute_defaults(): void
    {
        $hidden = new Hidden;

        $this->assertEquals([], $hidden->fields);
    }

    public function test_hidden_attribute_with_fields(): void
    {
        $hidden = new Hidden(fields: ['password', 'api_token', 'secret_key']);

        $this->assertEquals(['password', 'api_token', 'secret_key'], $hidden->fields);
    }

    public function test_encrypted_attribute_defaults(): void
    {
        $encrypted = new Encrypted;

        $this->assertEquals('AES-256-CBC', $encrypted->algorithm);
    }

    public function test_encrypted_attribute_with_custom_algorithm(): void
    {
        $encrypted = new Encrypted(algorithm: 'AES-128-CBC');

        $this->assertEquals('AES-128-CBC', $encrypted->algorithm);
    }

    public function test_hashed_attribute_defaults(): void
    {
        $hashed = new Hashed;

        $this->assertEquals('bcrypt', $hashed->algorithm);
        $this->assertEquals([], $hashed->options);
    }

    public function test_hashed_attribute_with_argon(): void
    {
        $hashed = new Hashed(algorithm: 'argon2id');

        $this->assertEquals('argon2id', $hashed->algorithm);
    }

    public function test_hashed_attribute_with_options(): void
    {
        $hashed = new Hashed(algorithm: 'bcrypt', options: ['rounds' => 12]);

        $this->assertEquals('bcrypt', $hashed->algorithm);
        $this->assertEquals(['rounds' => 12], $hashed->options);
    }

    public function test_multiple_hidden_fields(): void
    {
        $hidden = new Hidden(fields: [
            'password',
            'password_confirmation',
            'current_password',
            'new_password',
        ]);

        $this->assertCount(4, $hidden->fields);
    }
}
