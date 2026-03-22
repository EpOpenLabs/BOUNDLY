<?php

namespace Application\Posts\DTOs;

class CreatePostDTO
{
    public function __construct(
        public readonly string $title,
        public readonly string $content,
        public readonly int $user_id
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            $data['title'] ?? '',
            $data['content'] ?? '',
            $data['user_id'] ?? 0
        );
    }
}
