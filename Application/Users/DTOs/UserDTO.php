<?php

namespace Application\Users\DTOs;

/**
 * Example Application DTO: UserDTO
 * 
 * Used to transfer and structure data between Action and Domain layers.
 */
class UserDTO
{
    public function __construct(
        public readonly string $name,
        public readonly string $email,
        public readonly string $password,
        public readonly ?string $phone = null,
        public readonly ?string $address = null,
    ) {}

    public static function fromRequest(array $data): self
    {
        return new self(
            name:     $data['name'],
            email:    $data['email'],
            password: $data['password'],
            phone:    $data['phone'] ?? null,
            address:  $data['addres'] ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'name'     => $this->name,
            'email'    => $this->email,
            'password' => $this->password,
            'phone'    => $this->phone,
            'addres'   => $this->address,
        ];
    }
}
