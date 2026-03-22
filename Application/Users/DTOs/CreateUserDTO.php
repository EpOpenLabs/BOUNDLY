<?php
namespace Application\Users\DTOs;

use Infrastructure\FrameworkCore\Attributes\Required;
use Infrastructure\FrameworkCore\Attributes\Email;
use Infrastructure\FrameworkCore\Attributes\Min;

class CreateUserDTO
{
    public function __construct(
        #[Required]
        public readonly string $name,

        #[Required]
        #[Email]
        public readonly string $email,

        #[Required]
        #[Min(6)]
        public readonly string $password,

        public readonly ?string $phone = null,
        public readonly ?string $addres = null,
        public readonly ?string $avatar_url = null,
        public readonly ?string $bio = null
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            $data['name'] ?? '',
            $data['email'] ?? '',
            $data['password'] ?? '',
            $data['phone'] ?? null,
            $data['addres'] ?? null,
            $data['avatar_url'] ?? null,
            $data['bio'] ?? null
        );
    }
}
